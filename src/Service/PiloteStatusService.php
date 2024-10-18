<?php

namespace App\Service;

use App\Entity\Pilote;
use Doctrine\ORM\EntityManagerInterface;

class PiloteStatusService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Updates the statut and modifies firstdate by +1 or -1 year based on the statut.
     */
    public function updatePiloteStatus(Pilote $pilote): void
    {
        // Vérifie si le statut est false ou true pour déterminer l'action à appliquer
        if ($pilote->getStatut() === false) {
            // Statut est false, incrémente la date de 1 an
            $this->incrementFirstDate($pilote);
        } else {
            // Statut est true, décrémente la date de 1 an
            $this->decrementFirstDate($pilote);
        }

        // Sauvegarde les modifications dans la base de données
        $this->entityManager->persist($pilote);
        $this->entityManager->flush();
    }

    /**
     * Incrémenter la firstdate d'un an.
     */
    private function incrementFirstDate(Pilote $pilote): void
    {
        if ($pilote->getFirstdate() === null) {
            // Si la firstdate est null, initialise-la à aujourd'hui
            $pilote->setFirstdate(new \DateTime());
        } else {
            // Ajoute 1 an à la date existante
            $date = clone $pilote->getFirstdate();
            $date->modify('+1 year');
            $pilote->setFirstdate($date);
        }
    }

    /**
     * Décrémenter la firstdate d'un an.
     */
    private function decrementFirstDate(Pilote $pilote): void
    {
        if ($pilote->getFirstdate() === null) {
            // Si la firstdate est null, initialise-la à aujourd'hui
            $pilote->setFirstdate(new \DateTime());
        } else {
            // Retire 1 an de la date existante
            $date = clone $pilote->getFirstdate();
            $date->modify('-1 year');
            $pilote->setFirstdate($date);
        }
    }
}
