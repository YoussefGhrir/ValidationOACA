<?php

namespace App\Entity;

use App\Repository\CompagnieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompagnieRepository::class)]
class Compagnie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Pilote>
     */
    #[ORM\OneToMany(targetEntity: Pilote::class, mappedBy: 'compagnie', cascade: ['persist', 'remove'], orphanRemoval: false)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')] // Ensure pilots are not deleted, but their compagnie_id is set to NULL
    private Collection $pilotes;

    public function __construct()
    {
        $this->pilotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        if ($nom) {
            $this->nom = strtoupper($nom);
        } else {
            $this->nom = null;
        }

        return $this;
    }


    /**
     * @return Collection<int, Pilote>
     */
    public function getPilotes(): Collection
    {
        return $this->pilotes;
    }

    public function addPilote(Pilote $pilote): static
    {
        if (!$this->pilotes->contains($pilote)) {
            $this->pilotes->add($pilote);
            $pilote->setCompagnie($this);
        }

        return $this;
    }

    public function removePilote(Pilote $pilote): static
    {
        if ($this->pilotes->removeElement($pilote)) {
            // Set the owning side to null (unless already changed)
            if ($pilote->getCompagnie() === $this) {
                $pilote->setCompagnie(null);
            }
        }

        return $this;
    }
}
