<?php

namespace App\Controller;

use App\Entity\Compagnie;
use App\Form\CompagnieType;
use App\Repository\CompagnieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compagnie')]
class CompagnieController extends AbstractController
{
    #[Route('/', name: 'app_compagnie_index', methods: ['GET'])]
    public function index(CompagnieRepository $compagnieRepository): Response
    {
        return $this->render('compagnie/index.html.twig', [
            'compagnies' => $compagnieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_compagnie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $compagnie = new Compagnie();
        $form = $this->createForm(CompagnieType::class, $compagnie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($compagnie);
            $entityManager->flush();

            return $this->redirectToRoute('app_compagnie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('compagnie/new.html.twig', [
            'compagnie' => $compagnie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_compagnie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Compagnie $compagnie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompagnieType::class, $compagnie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_compagnie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('compagnie/edit.html.twig', [
            'compagnie' => $compagnie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_compagnie_delete', methods: ['POST'])]
    public function delete(Request $request, Compagnie $compagnie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$compagnie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($compagnie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_compagnie_index', [], Response::HTTP_SEE_OTHER);
    }
}
