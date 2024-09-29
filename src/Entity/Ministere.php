<?php

namespace App\Entity;

use App\Repository\MinistereRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MinistereRepository::class)]
#[UniqueEntity(fields: ['nom'], message: 'Ce nom de ministère existe déjà.')]
class Ministere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $nom;

    // Getter pour l'attribut 'id'
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter et setter pour l'attribut 'nom'
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }
}
