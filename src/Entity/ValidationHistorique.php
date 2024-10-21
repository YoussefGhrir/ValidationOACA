<?php

namespace App\Entity;

use App\Repository\ValidationHistoriqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValidationHistoriqueRepository::class)]
class ValidationHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDelivree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValideJusquau = null;

    #[ORM\ManyToOne(inversedBy: 'validationHistoriques')]
    private ?Pilote $pilote = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDelivree(): ?\DateTimeInterface
    {
        return $this->dateDelivree;
    }

    public function setDateDelivree(?\DateTimeInterface $dateDelivree): static
    {
        $this->dateDelivree = $dateDelivree;

        return $this;
    }

    public function getDateValideJusquau(): ?\DateTimeInterface
    {
        return $this->dateValideJusquau;
    }

    public function setDateValideJusquau(?\DateTimeInterface $dateValideJusquau): static
    {
        $this->dateValideJusquau = $dateValideJusquau;

        return $this;
    }

    public function getPilote(): ?Pilote
    {
        return $this->pilote;
    }

    public function setPilote(?Pilote $pilote): static
    {
        $this->pilote = $pilote;

        return $this;
    }
}
