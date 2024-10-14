<?php

namespace App\DataFixtures;

use App\Entity\Avion;
use App\Entity\Compagnie;
use App\Entity\Directeur; // Il manquait aussi l'import pour Directeur
use App\Entity\Ministere;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
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

        // Ajout de quelques compagnies
        $compagnie1 = new Compagnie();
        $compagnie1->setNom('Nouvelair');
        $manager->persist($compagnie1);

        $compagnie2 = new Compagnie();
        $compagnie2->setNom('Express Air Cargo');
        $manager->persist($compagnie2);

        $compagnie3 = new Compagnie();
        $compagnie3->setNom('Tunisair');
        $manager->persist($compagnie3);

        $compagnie4 = new Compagnie();
        $compagnie4->setNom('Tunisavia');
        $manager->persist($compagnie4);

        $compagnie5 = new Compagnie();
        $compagnie5->setNom('Tunisair Express');
        $manager->persist($compagnie5);

        // Ajout de quelques avions
        $avion1 = new Avion();
        $avion1->setNom('a320');
        $manager->persist($avion1);

        $avion2 = new Avion();
        $avion2->setNom('a330');
        $manager->persist($avion2);

        $avion3 = new Avion();
        $avion3->setNom('B737 300-800');
        $manager->persist($avion3);

        // Sauvegarde en base de données
        $manager->flush();
    }
}
