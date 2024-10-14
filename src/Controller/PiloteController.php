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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pilote')]
class PiloteController extends AbstractController
{
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
            // Récupérer l'utilisateur connecté
            $user = $this->getUser(); // Utilisateur connecté

            // Vérifier si l'utilisateur est défini
            if ($user) {
                // Associer l'utilisateur connecté comme créateur du pilote
                $pilote->setCreatedBy($user);
            } else {
                // Si aucun utilisateur connecté, lever une exception ou gérer l'erreur
                throw $this->createAccessDeniedException('Utilisateur non connecté.');
            }

            // Persister et enregistrer le pilote
            $entityManager->persist($pilote);
            $entityManager->flush();

            // Stocker le message de succès dans la session
            $session->getFlashBag()->add('success', 'Pilote ajouté avec succès par ' . $user->getFirstname() . ' ' . $user->getLastname());

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
            // Récupérer le type du pilote avant de le supprimer
            $piloteType = $pilote->getType();

            // Supprimer le pilote
            $entityManager->remove($pilote);
            $entityManager->flush();

            // Redirection en fonction du type de pilote
            if ($piloteType === false) {
                return $this->redirectToRoute('app_pilote_atpl', [], Response::HTTP_SEE_OTHER);
            } elseif ($piloteType === true) {
                return $this->redirectToRoute('app_pilote_cpl', [], Response::HTTP_SEE_OTHER);
            } else { // Pour le type 'Double'
                return $this->redirectToRoute('app_pilote_double', [], Response::HTTP_SEE_OTHER);
            }
        }

        // Redirection par défaut si le token CSRF est invalide
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

    #[Route('/pilote/update-statut/{id}', name: 'update_statut', methods: ['POST'])]
    public function updateStatut(Request $request, Pilote $pilote, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['statut'])) {
                $pilote->setStatut((bool)$data['statut']); // Cast pour s'assurer que le statut est un booléen
                $entityManager->persist($pilote);
                $entityManager->flush();

                return new JsonResponse(['success' => true], 200);
            } else {
                return new JsonResponse(['error' => 'Données invalides'], 400);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la mise à jour du statut : ' . $e->getMessage()], 500);
        }
    }
}