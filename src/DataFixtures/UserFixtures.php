<?php

namespace App\DataFixtures;

use App\Entity\Directeur;
use App\Entity\Ministere;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class
UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur admin
        $admin = new User();
        $admin->setEmail('oaca@gmail.com');
        $admin->setFirstname('OACA');
        $admin->setLastname('ADMIN');
        $admin->setRoles(['ROLE_ADMIN']);

        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'oaca2024');
        $admin->setPassword($hashedPassword);

        // Persiste et sauvegarde l'utilisateur
        $manager->persist($admin);

        $directeur = new Directeur();
        $directeur->setNom('ANIS BEN HADJ NASR');
        $directeur->setFonction('AIRWORTHINESS DIRECTOR');
        $manager->persist($directeur);

        // Création de quelques ministères
        $ministere = new Ministere();
        $ministere->setNom('Ministère du Transport et de la Logistique');
        $manager->persist($ministere);
        $manager->flush();
    }
}
