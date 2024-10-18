<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class UserController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/users', name: 'user_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        // Check if the user has the ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette page. Veuillez contacter l\'administrateur.');
            return $this->redirectToRoute('app_home'); // Replace 'app_home' with your home page route
        }

        // Get the currently logged-in user's ID
        $loggedInUserId = $this->getUser()->getId();

        // Retrieve all users except the logged-in user
        $users = $entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.id != :currentUserId')
            ->setParameter('currentUserId', $loggedInUserId)
            ->getQuery()
            ->getResult();

        return $this->render('user/indexuser.html.twig', [
            'users' => $users,
        ]);
    }
    #[Route('/user/delete/{id}', name: 'user_delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        // Check if the user has the ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer des utilisateurs. Veuillez contacter l\'administrateur.');
            return $this->redirectToRoute('app_home'); // Replace 'app_home' with your home page route
        }

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');

        return $this->redirectToRoute('user_list');
    }

    #[Route('/user/{id}', name: 'user_show', requirements: ['id' => '\d+'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        // Check if the user has the ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à consulter cette page. Veuillez contacter l\'administrateur.');
            return $this->redirectToRoute('app_home'); // Replace 'app_home' with your home page route
        }

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        return $this->render('user/showuser.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/edit/{id}', name: 'user_edit', requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request,
        UserPasswordHasherInterface $passwordHasher // Ajouter le hachage du mot de passe
    ): Response
    {
        // Vérification des droits
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier des utilisateurs. Veuillez contacter l\'administrateur.');
            return $this->redirectToRoute('app_home'); // Remplace 'app_home' par ta route d'accueil
        }

        // Récupération de l'utilisateur à modifier
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Création du formulaire
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification du champ mot de passe
            $plainPassword = $form->get('password')->getData();

            if ($plainPassword) {
                // Hachage du mot de passe avant mise à jour
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Sauvegarder les modifications
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash('success', 'Utilisateur mis à jour avec succès');

            return $this->redirectToRoute('user_list');
        }

        // Affichage du formulaire de modification
        return $this->render('user/edituser.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }}
