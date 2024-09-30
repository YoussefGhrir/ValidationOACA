<?php

namespace App\Controller;

use App\Entity\Avion;
use App\Entity\Compagnie;
use App\Entity\Pilote;
use App\Form\PiloteType;
use App\Repository\PiloteRepository;
use App\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pilote')]
class PiloteController extends AbstractController
{
    #[Route('/', name: 'app_pilote_index', methods: ['GET'])]
    public function index(PiloteRepository $piloteRepository): Response
    {
        return $this->render('pilote/index.html.twig', [
            'pilotes' => $piloteRepository->findAll(),
        ]);
    }

    #[Route('/ATPL', name: 'app_pilote_atpl', methods: ['GET'])]
    public function atpl(PiloteRepository $piloteRepository): Response
    {
        $atplPilotes = $piloteRepository->findBy(['type' => false]); // ATPL

        return $this->render('pilote/index_atpl.html.twig', [
            'pilotes' => $atplPilotes,
        ]);

    }

    #[Route('/CPL', name: 'app_pilote_cpl', methods: ['GET'])]
    public function cpl(PiloteRepository $piloteRepository): Response
    {
        $cplPilotes = $piloteRepository->findBy(['type' => true]); // CPL

        return $this->render('pilote/index_cpl.html.twig', [
            'pilotes' => $cplPilotes,
        ]);
    }

    #[Route('/DOUBLE', name: 'app_pilote_double', methods: ['GET'])]
    public function double(PiloteRepository $piloteRepository): Response
    {
        $doublePilotes = $piloteRepository->findBy(['type' => null]); // CPL

        return $this->render('pilote/index_double.html.twig', [
            'pilotes' => $doublePilotes,
        ]);
    }

    #[Route('/new', name: 'app_pilote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $pilote = new Pilote();

        // Créez le formulaire avec l'option default_type définie sur false pour 'ATPL'
        $form = $this->createForm(PiloteType::class, $pilote, [
            'default_type' => false, // Définir 'ATPL' comme valeur par défaut
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pilote);
            $entityManager->flush();

            // Stocke le message de succès dans la session
            $session->getFlashBag()->add('success', 'Pilote ajouté avec succès !');

            // Redirige vers la même page pour ajouter un autre pilote
            return $this->redirectToRoute('app_pilote_new');
        }

        return $this->render('pilote/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_pilote_show', methods: ['GET'])]
    public function show(Pilote $pilote): Response
    {
        return $this->render('pilote/show.html.twig', [
            'pilote' => $pilote,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pilote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pilote $pilote, EntityManagerInterface $entityManager): Response
    {
        // Créer le formulaire en passant 'is_update' et 'default_type' pour le type actuel du pilote
        $form = $this->createForm(PiloteType::class, $pilote, [
            'is_update' => true,
            'default_type' => $pilote->getTypeLabel(), // Passer le type actuel comme valeur par défaut
        ]);

        // Gérer la requête
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les changements dans la base de données
            $entityManager->flush();

            // Ajouter un message flash de succès
            $this->addFlash('success', 'Le pilote a été modifié avec succès.');

            // Rediriger vers la page d'édition pour ce pilote
            return $this->redirectToRoute('app_pilote_edit', ['id' => $pilote->getId()]);
        }

        // Afficher le formulaire d'édition
        return $this->render('pilote/edit.html.twig', [
            'pilote' => $pilote,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_pilote_delete', methods: ['POST'])]
    public function delete(Request $request, Pilote $pilote, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $pilote->getId(), $request->request->get('_token'))) {
            $entityManager->remove($pilote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pilote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/pilote/generate-pdf/{id}', name: 'pilote_generate_pdf')]
    public function generatePdf(Pilote $pilote, PdfGenerator $pdfGenerator): Response
    {
        // Vérification que le pilote a bien sélectionné une compagnie et un avion
        if (!$pilote->getCompagnie()) {
            throw $this->createNotFoundException('Le pilote n\'a pas sélectionné de compagnie.');
        }

        if (!$pilote->getAvion()) {
            throw $this->createNotFoundException('Le pilote n\'a pas sélectionné d\'avion.');
        }

        // Préparation des données à passer au template
        $data = [
            'pilote' => $pilote,
            'compagnie' => $pilote->getCompagnie(),
            'avion' => $pilote->getAvion(),
        ];

        // Générer le PDF avec les données du pilote, de la compagnie, et de l'avion
        return $pdfGenerator->generatePdf($pilote, 'pdf/pdf_template.html.twig', $data);
    }
}
