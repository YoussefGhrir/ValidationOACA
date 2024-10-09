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
        // Vérifier si l'utilisateur connecté a le rôle "ROLE_ADMIN"
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à la page d\'administration.');
            return $this->redirectToRoute('app_home');
        }

        // Créer un nouvel utilisateur
        $newUser = new User();

        // Créer le formulaire
        $form = $this->createForm(RegistrationFormType::class, $newUser);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $newUser->setPassword(
                $userPasswordHasher->hashPassword(
                    $newUser,
                    $form->get('plainPassword')->getData()
                )
            );

            // Récupérer le rôle sélectionné et le convertir en tableau
            $selectedRole = $form->get('roles')->getData();
            $newUser->setRoles([$selectedRole]);

            // Sauvegarder l'utilisateur
            $entityManager->persist($newUser);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
