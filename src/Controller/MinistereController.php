<?php

namespace App\Controller;

use App\Entity\Ministere;
use App\Form\MinistereType;
use App\Repository\MinistereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ministere')]
class MinistereController extends AbstractController
{
    #[Route('/', name: 'app_ministere_index', methods: ['GET'])]
    public function index(MinistereRepository $ministereRepository): Response
    {
        return $this->render('ministere/index.html.twig', [
            'ministeres' => $ministereRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ministere_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ministere = new Ministere();
        $form = $this->createForm(MinistereType::class, $ministere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ministere);
            $entityManager->flush();

            return $this->redirectToRoute('app_ministere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ministere/new.html.twig', [
            'ministere' => $ministere,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ministere_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ministere $ministere, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MinistereType::class, $ministere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ministere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ministere/edit.html.twig', [
            'ministere' => $ministere,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ministere_delete', methods: ['POST'])]
    public function delete(Request $request, Ministere $ministere, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ministere->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ministere);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ministere_index', [], Response::HTTP_SEE_OTHER);
    }
}
