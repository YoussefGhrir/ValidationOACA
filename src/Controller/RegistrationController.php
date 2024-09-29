<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Create a new User object
        $user = new User();

        // Create the form based on RegistrationFormType
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Handle form submission
        if ($form->isSubmitted()) {
            // Check if the form is valid
            if ($form->isValid()) {
                // Encode and set the password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                // Save the user to the database
                $entityManager->persist($user);
                $entityManager->flush();

                // Optionally redirect to login page or another page
                return $this->redirectToRoute('app_login');
            } else {
                // Flash message for form errors
                $this->addFlash('error', 'There are errors in the form. Please fix them and try again.');
            }
        }

        // Render the registration form template with the form view
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
