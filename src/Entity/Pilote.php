<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Repository\PiloteRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[AllowDynamicProperties]
#[ORM\Entity(repositoryClass: PiloteRepository::class)]
class Pilote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $datebirth = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $firstdate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $validite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $datelangue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pays = null;

    #[ORM\ManyToOne(inversedBy: 'pilotes')]
    private ?User $adminpilot = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $nationalite = null;

    #[ORM\Column(nullable: true)]
    private ?bool $type;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonction = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $datequalif = null;

    #[ORM\ManyToOne(inversedBy: 'avion')]
    private ?Compagnie $compagnie = null;

    #[ORM\ManyToOne(inversedBy: 'pilotes')]
    private ?Avion $avion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = strtoupper($nom);
        return $this;
    }



    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        if ($prenom) {
            $this->prenom = strtoupper($prenom);
        }
        return $this;
    }

    public function getDatebirth(): ?DateTimeInterface
    {
        return $this->datebirth;
    }

    public function setDatebirth(?string $datebirth): static
    {
        if ($datebirth) {
            $this->datebirth = DateTime::createFromFormat('Y-m-d', $datebirth) ?: null;
        } else {
            $this->datebirth = null;
        }
        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getFirstdate(): ?DateTimeInterface
    {
        return $this->firstdate;
    }

    public function setFirstdate(?string $firstdate): static
    {
        if ($firstdate) {
            $this->firstdate = DateTime::createFromFormat('Y-m-d', $firstdate) ?: null;
        } else {
            $this->firstdate = null;
        }
        return $this;
    }

    public function getValidite(): ?DateTimeInterface
    {
        return $this->validite;
    }

    public function setValidite(?string $validite): static
    {
        if ($validite) {
            $this->validite = DateTime::createFromFormat('Y-m-d', $validite) ?: null;
        } else {
            $this->validite = null;
        }
        return $this;
    }

    public function getDatelangue(): ?DateTimeInterface
    {
        return $this->datelangue;
    }

    public function setDatelangue(?string $datelangue): static
    {
        if ($datelangue) {
            $this->datelangue = DateTime::createFromFormat('Y-m-d', $datelangue) ?: null;
        } else {
            $this->datelangue = null;
        }
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;
        return $this;
    }

    public function getAdminpilot(): ?User
    {
        return $this->adminpilot;
    }

    public function setAdminpilot(?User $adminpilot): static
    {
        $this->adminpilot = $adminpilot;
        return $this;
    }

    public function getNationalite(): ?string
    {
        return $this->nationalite;
    }

    public function setNationalite(?string $nationalite): static
    {
        $this->nationalite = $nationalite;
        return $this;
    }

    public function isType(): ?bool
    {
        return $this->type;
    }
    public function __construct()
    {
        // Initialize the type property
        $this->type = false;  // Default value
    }
    public function setType(?bool $type): static
    {
        $this->type = $type;
        return $this;
    }
    public function getType(): ?bool
    {
        return $this->type;
    }
    public function getTypeLabel(): string
    {
        if ($this->type === null) {
            return 'DOUBLE';
        }

        return $this->type ? 'CPL' : 'ATPL';
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;
        return $this;
    }

    public function getDatequalif(): ?DateTimeInterface
    {
        return $this->datequalif;
    }

    public function setDatequalif(?string $datequalif): static
    {
        if ($datequalif) {
            $this->datequalif = DateTime::createFromFormat('Y-m-d', $datequalif) ?: null;
        } else {
            $this->datequalif = null;
        }
        return $this;
    }

    // Getters and Setters for Compagnie and Avion
    public function getCompagnie(): ?Compagnie
    {
        return $this->compagnie;
    }

    public function setCompagnie(?Compagnie $compagnie): self
    {
        $this->compagnie = $compagnie;
        return $this;
    }

    public function getAvion(): ?Avion
    {
        return $this->avion;
    }

    public function setAvion(?Avion $avion): self
    {
        $this->avion = $avion;
        return $this;
    }

}
