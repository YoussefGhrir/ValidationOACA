<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Ministere;
use App\Entity\ValidationHistorique;
use App\Entity\Directeur;
use App\Entity\Pilote;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class PdfGenerator
{
    private Environment $twig;
    private EntityManagerInterface $entityManager;
    private string $counterFile = 'counter.txt'; // Fichier pour stocker le compteur global

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    // Méthode pour gérer le numéro de PDF et l'année en cours pour un pilote
    private function getNumberAndYearForPilote(Pilote $pilote): array
    {
        $year = date('Y'); // Année en cours

        if ($pilote->getPdfNumber() === null) {
            $pdfNumber = $this->getNextGlobalPdfNumber();
            $pilote->setPdfNumber($pdfNumber);
            $this->entityManager->persist($pilote);
            $this->entityManager->flush();
        } else {
            $pdfNumber = $pilote->getPdfNumber();
        }

        $formattedCounter = str_pad((string)$pdfNumber, 3, '0', STR_PAD_LEFT);

        return [$formattedCounter, $year];
    }

    private function getNextGlobalPdfNumber(): int
    {
        $counter = $this->getCurrentCounter();
        $counter = ($counter + 1) % 1000;
        $this->updateCounter($counter);

        return $counter;
    }

    private function getCurrentCounter(): int
    {
        if (file_exists($this->counterFile)) {
            return (int)file_get_contents($this->counterFile);
        }

        return 0;
    }

    private function updateCounter(int $counter): void
    {
        file_put_contents($this->counterFile, (string)$counter);
    }

    public function generatePdf(Pilote $pilote, string $template, array $data): Response
    {
        // Récupération des directeurs depuis l'entité Directeur
        $directeurs = $this->entityManager->getRepository(Directeur::class)->findAll();
        $data['directeurs'] = $directeurs;
        $ministeres = $this->entityManager->getRepository(Ministere::class)->findAll();
        $data['ministeres'] = $directeurs;

        [$formattedCounter, $year] = $this->getNumberAndYearForPilote($pilote);
        $data['numero_pdf'] = $formattedCounter;
        $data['current_year'] = $year;

        $datelangue = $pilote->getDatelangue();
        $datequalif = $pilote->getDatequalif();
        $firstDate = $pilote->getFirstdate();

        $dateValideJusquAu = $this->calculateDateValideJusquAu($pilote, $datelangue, $datequalif, $firstDate);

        if ($dateValideJusquAu) {
            $dateValideJusquAu->modify('-1 day');
            $data['valide_jusquau'] = $dateValideJusquAu->format('d/m/Y');
        } else {
            $data['valide_jusquau'] = 'Non défini';
        }

        $data['privilegefr'] = $pilote->getPrivilegefr() ?? '';
        $data['privilegeag'] = $pilote->getPrivilegeag() ?? '';
        $dateDelivreeLe = new \DateTime();

        $this->saveValidationHistorique($pilote, $dateDelivreeLe, $dateValideJusquAu);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $html = $this->twig->render($template, $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $type = strtoupper((string)$pilote->getType());
        $name = ucfirst($pilote->getNom());
        $filename = sprintf('%s-%s.pdf', $type, $name);

        $pdfOutput = $dompdf->output();

        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function calculateDateValideJusquAu(Pilote $pilote, ?\DateTime $datelangue, ?\DateTime $datequalif, ?\DateTime $firstDate): ?\DateTime
    {
        $dateValideJusquAu = null;

        // Calculer la date limite basée sur l'âge de 65 ans
        $dateAnniversaire65 = (clone $pilote->getDatebirth())->modify('+65 years');
        $today = new \DateTime();

        if ($dateAnniversaire65 <= $today) {
            // Le pilote a déjà 65 ans ou plus
            return null;
        }

        // Si le pilote approche ses 65 ans, limiter la validité à cette date
        if ($datelangue && $datequalif) {
            $dateValideJusquAu = min($datelangue, $datequalif, $dateAnniversaire65);
        } elseif ($datelangue) {
            $dateValideJusquAu = min($datelangue, $dateAnniversaire65);
        } elseif ($datequalif) {
            $dateValideJusquAu = min($datequalif, $dateAnniversaire65);
        } else {
            $dateValideJusquAu = $dateAnniversaire65;
        }

        return $dateValideJusquAu;
    }

    private function saveValidationHistorique(Pilote $pilote, \DateTime $dateDelivree, ?\DateTime $dateValideJusquAu): void
    {
        $existingValidation = $this->entityManager->getRepository(ValidationHistorique::class)
            ->findOneBy([
                'pilote' => $pilote,
                'dateDelivree' => $dateDelivree,
                'dateValideJusquau' => $dateValideJusquAu
            ]);

        if ($existingValidation) {
            return;
        }

        $historique = new ValidationHistorique();
        $historique->setPilote($pilote);
        $historique->setDateDelivree($dateDelivree);
        $historique->setDateValideJusquau($dateValideJusquAu);

        $this->entityManager->persist($historique);
        $this->entityManager->flush();
    }
}
