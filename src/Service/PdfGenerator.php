<?php
// src/Service/PdfGenerator.php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Pilote;
use App\Entity\Ministere;

class PdfGenerator
{
    private $twig;
    private EntityManagerInterface $entityManager;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }


    public function generatePdf($template, $data): Response
    {

        // Récupérer tous les ministères
        $ministeres = $this->entityManager->getRepository(Ministere::class)->findAll();

        // Ajouter la liste des ministères aux données passées au template
        $data['ministeres'] = $ministeres;

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
            'Content-Disposition' => 'inline; filename="pilote_information.pdf"',
        ]);
    }
}
