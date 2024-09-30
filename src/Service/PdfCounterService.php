<?php
// src/Service/PdfCounterService.php

namespace App\Service;

class PdfCounterService
{
    private $counterFile = 'global_pdf_counter.txt'; // Fichier pour stocker le compteur global

    public function getNextPdfNumber(): int
    {
        // Récupérer le compteur actuel depuis le fichier, ou initialiser à 0
        $counter = file_exists($this->counterFile) ? (int)file_get_contents($this->counterFile) : 0;

        // Incrémenter le compteur
        $counter += 1;

        // Mettre à jour le fichier avec le nouveau compteur
        file_put_contents($this->counterFile, $counter);

        return $counter;
    }
}
