<?php
// src/Service/PdfGenerator.php

namespace App\Service;

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

        // Configurer Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        // Charger le template Twig
        $html = $this->twig->render($template, $data);
        $dompdf->loadHtml($html);

        // Définir la taille du papier et l'orientation
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF
        $dompdf->render();

        // Sortie du PDF
        $pdfOutput = $dompdf->output();

        // Retourner le PDF comme réponse
        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
    }
}
