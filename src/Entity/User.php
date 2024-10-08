<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Un compte avec cet e-mail existe déjà')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: 'integer')]
private ?int $id = null;

#[ORM\Column(type: 'string', length: 180, unique: true)]
private ?string $email = null;

#[ORM\Column(type: 'json')]
private array $roles = [];

#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $password = null;

#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $firstname = null;

#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $lastname = null;

public function getId(): ?int
{
return $this->id;
}

public function getEmail(): ?string
{
return $this->email;
}

public function setEmail(string $email): static
{
$this->email = $email;

return $this;
}

public function getUserIdentifier(): string
{
return (string) $this->email;
}

public function getRoles(): array
{
$roles = $this->roles;
$roles[] = 'ROLE_USER';

return array_unique($roles);
}

public function setRoles(array $roles): static
{
$this->roles = $roles;

return $this;
}

public function eraseCredentials(): void
{
// Clear sensitive data here if necessary
}

public function getPassword(): ?string
{
return $this->password;
}

public function setPassword(?string $password): static
{
$this->password = $password;

return $this;
}

public function getFirstname(): ?string
{
return $this->firstname;
}

public function setFirstname(?string $firstname): static
{
$this->firstname = $firstname;

return $this;
}

public function getLastname(): ?string
{
return $this->lastname;
}

public function setLastname(?string $lastname): static
{
$this->lastname = $lastname;

return $this;
}
}