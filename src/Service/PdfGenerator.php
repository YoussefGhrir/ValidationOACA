<?php
// src/Service/PdfGenerator.php

namespace App\Service;

use App\Entity\ValidationHistorique;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Directeur;
use App\Entity\Ministere;
use App\Entity\Pilote;

class PdfGenerator
{
    private $twig;
    private EntityManagerInterface $entityManager;
    private $counterFile = 'counter.txt'; // Fichier pour stocker le compteur global

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    // Méthode pour gérer le numéro de PDF et l'année en cours pour un pilote
    private function getNumberAndYearForPilote(Pilote $pilote): array
    {
        $year = date('Y'); // Année en cours

        // Vérifier si le pilote a déjà un numéro de PDF
        if ($pilote->getPdfNumber() === null) {
            // Si le pilote n'a pas de numéro, obtenir le prochain numéro global
            $pdfNumber = $this->getNextGlobalPdfNumber();
            $pilote->setPdfNumber($pdfNumber);

            // Sauvegarder le numéro de PDF pour le pilote
            $this->entityManager->persist($pilote);
            $this->entityManager->flush();
        } else {
            // Utiliser le numéro déjà assigné
            $pdfNumber = $pilote->getPdfNumber();
        }

        // Formatter le compteur avec 3 chiffres (ex: 001, 002, ...)
        $formattedCounter = str_pad($pdfNumber, 3, '0', STR_PAD_LEFT);

        return [$formattedCounter, $year];
    }

    // Méthode pour récupérer le compteur global actuel et l'incrémenter
    private function getNextGlobalPdfNumber(): int
    {
        // Récupérer le compteur actuel depuis le fichier
        $counter = $this->getCurrentCounter();

        // Incrémenter le compteur, le remettre à 000 si on atteint 999
        $counter = ($counter + 1) % 1000;

        // Mettre à jour le fichier avec le nouveau compteur
        $this->updateCounter($counter);

        return $counter;
    }

    // Méthode pour récupérer le compteur actuel depuis le fichier
    private function getCurrentCounter(): int
    {
        if (file_exists($this->counterFile)) {
            return (int)file_get_contents($this->counterFile);
        }

        // Si le fichier n'existe pas, retourner 0
        return 0;
    }

    // Méthode pour mettre à jour le compteur dans le fichier
    private function updateCounter(int $counter): void
    {
        file_put_contents($this->counterFile, $counter);
    }

    public function generatePdf(Pilote $pilote, $template, $data): Response
    {
        // Récupérer tous les directeurs
        $directeurs = $this->entityManager->getRepository(Directeur::class)->findAll();
        $data['directeurs'] = $directeurs;

        // Récupérer tous les ministères (si nécessaire)
        $ministeres = $this->entityManager->getRepository(Ministere::class)->findAll();
        $data['ministeres'] = $ministeres;

        // Récupérer le numéro et l'année pour le pilote
        [$formattedCounter, $year] = $this->getNumberAndYearForPilote($pilote);
        $data['numero_pdf'] = $formattedCounter;
        $data['current_year'] = $year;

        // Récupérer les dates de qualification et de langue
        $datelangue = $pilote->getDatelangue();
        $datequalif = $pilote->getDatequalif();
        $firstDate = $pilote->getFirstdate();

        // Initialiser la date de validité
        $dateValideJusquAu = null;
        $isBeyondOneYear = false; // Indicateur pour savoir si les dates dépassent la limite d'un an

        // Si le type est ATPL, appliquer les règles spécifiques
        if ($pilote->getTypeLabel() === 'ATPL' && $firstDate) {
            // Date limite pour ATPL : 2 ans après la date de première délivrance
            $twoYearsAfterFirstDate = (clone $firstDate)->modify('+2 years');
            $oneYearAfterFirstDate = (clone $firstDate)->modify('+1 year');

            // Si datelangue et datequalif sont toutes les deux après 1 an de firstDate
            if (
                ($datelangue && $datelangue > $oneYearAfterFirstDate) &&
                ($datequalif && $datequalif > $oneYearAfterFirstDate)
            ) {
                // Fixer la date de validité à 1 an après la première délivrance
                $dateValideJusquAu = $oneYearAfterFirstDate;
                $isBeyondOneYear = true; // Indiquer que les dates ont dépassé la limite d'un an
            } else {
                // Sinon, prendre la date la plus proche entre datelangue et datequalif
                $validDates = array_filter([$datelangue, $datequalif], function ($date) use ($twoYearsAfterFirstDate) {
                    return $date <= $twoYearsAfterFirstDate;
                });

                if (!empty($validDates)) {
                    $dateValideJusquAu = min($validDates);
                } else {
                    // Si aucune date n'est valide, on limite à 1 an après la première délivrance
                    $dateValideJusquAu = $oneYearAfterFirstDate;
                    $isBeyondOneYear = true; // Indiquer que les dates ont dépassé la limite d'un an
                }
            }
        } else {
            // Pour le type CPL ou si le type n'est pas défini, prendre la date la plus ancienne entre datelangue et datequalif
            if ($datelangue && $datequalif) {
                $dateValideJusquAu = min($datelangue, $datequalif);
            } elseif ($datelangue) {
                $dateValideJusquAu = $datelangue;
            } elseif ($datequalif) {
                $dateValideJusquAu = $datequalif;
            }
        }

        // Ajouter la date minimale au template (formatée si nécessaire)
        $data['valide_jusquau'] = $dateValideJusquAu ? $dateValideJusquAu->format('d/m/Y') : 'Non défini';
        $data['is_beyond_one_year'] = $isBeyondOneYear; // Ajouter l'indicateur au template

        // Ajouter les privilèges au template
        $data['privilegefr'] = $pilote->getPrivilegefr() ?? '';
        $data['privilegeag'] = $pilote->getPrivilegeag() ?? '';
        // Capturer la date actuelle pour "Délivrée le"
        $dateDelivreeLe = new \DateTime();

        // Enregistrer les dates dans l'historique
        $this->saveValidationHistorique($pilote, $dateDelivreeLe, $dateValideJusquAu);


        // Configurer Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $html = $this->twig->render($template, $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le nom de fichier basé sur le type et le nom du pilote
        $type = strtoupper($pilote->getType());
        $name = ucfirst($pilote->getNom());
        $filename = sprintf('%s-%s.pdf', $type, $name);

        // Sortie du PDF
        $pdfOutput = $dompdf->output();

        // Retourner le PDF comme réponse avec le nom de fichier dynamique
        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
    // Méthode pour sauvegarder les dates dans l'historique des validations
// Méthode pour sauvegarder les dates dans l'historique des validations
    private function saveValidationHistorique(Pilote $pilote, \DateTime $dateDelivree, ?\DateTime $dateValideJusquAu): void
    {
        // Rechercher si une validation avec les mêmes dates existe déjà pour ce pilote
        $existingValidation = $this->entityManager->getRepository(ValidationHistorique::class)
            ->findOneBy([
                'pilote' => $pilote,
                'dateDelivree' => $dateDelivree,
                'dateValideJusquau' => $dateValideJusquAu
            ]);

        // Si une validation existe déjà avec les mêmes dates, ne pas enregistrer à nouveau
        if ($existingValidation) {
            return; // On ne fait rien, l'enregistrement existe déjà
        }

        // Si aucune validation identique n'existe, on enregistre la nouvelle
        $historique = new ValidationHistorique();
        $historique->setPilote($pilote);
        $historique->setDateDelivree($dateDelivree);  // Date "Délivrée le"
        $historique->setDateValideJusquau($dateValideJusquAu); // Date "Valide jusqu'au"

        $this->entityManager->persist($historique);
        $this->entityManager->flush();
    }



}
