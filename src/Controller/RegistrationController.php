<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegistrationController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $this->security->getUser();

        // Vérifier si l'utilisateur connecté est bien "oaca@gmail.com"
        if (!$user || $user->getEmail() !== 'oaca@gmail.com') {
            // Ajouter un message flash personnalisé pour l'utilisateur non autorisé
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à la page d\'administration. Veuillez contacter l\'administrateur.');

            // Rediriger l'utilisateur vers la page d'accueil sans déconnexion ou suppression
            return $this->redirectToRoute('app_home'); // Remplacez 'app_home' par la route de votre page d'accueil
        }

        // Créer un nouvel objet User pour l'inscription d'un autre utilisateur (pas l'utilisateur connecté)
        $newUser = new User();

        // Créer le formulaire basé sur RegistrationFormType
        $form = $this->createForm(RegistrationFormType::class, $newUser);
        $form->handleRequest($request);

        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder et définir le mot de passe pour le nouvel utilisateur
            $newUser->setPassword(
                $userPasswordHasher->hashPassword(
                    $newUser,
                    $form->get('plainPassword')->getData()
                )
            );

            // Sauvegarder le nouvel utilisateur dans la base de données
            $entityManager->persist($newUser);
            $entityManager->flush();

            // Rediriger vers la page de connexion ou autre
            return $this->redirectToRoute('app_login');
        }

        // Rendre le template du formulaire d'inscription
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
