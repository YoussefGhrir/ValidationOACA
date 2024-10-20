<?php

namespace App\Controller;

use App\Entity\Avion;
use App\Entity\Compagnie;
use App\Entity\Pilote;
use App\Form\PiloteType;
use App\Repository\PiloteRepository;
use App\Service\PdfGenerator;
use App\Service\PiloteStatusService;
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
    private $piloteStatusService;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, PiloteStatusService $piloteStatusService)
    {
        $this->entityManager = $entityManager;
        $this->piloteStatusService = $piloteStatusService;
    }

    #[Route('/ATPL', name: 'app_pilote_atpl', methods: ['GET'])]
    public function atpl(PiloteRepository $piloteRepository): Response
    {
        // Récupérer les pilotes ATPL sans modifier leur statut
        $atplPilotes = $piloteRepository->findBy(['type' => false]);

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
        // Cloner l'entité Pilote pour conserver l'état avant modification
        $originalPilote = clone $pilote;

        // Créer le formulaire en passant 'is_update' et 'default_type' pour le type actuel du pilote
        $form = $this->createForm(PiloteType::class, $pilote, [
            'is_update' => true,
            'default_type' => $pilote->getTypeLabel(), // Passer le type actuel comme valeur par défaut
        ]);

        // Gérer la requête
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder le type avant modification
            $oldType = $originalPilote->getType();
            $newType = $pilote->getType();  // Type après la modification

            // Vérifier si le type a changé
            if ($oldType !== $newType) {
                // Cloner l'original et conserver son ancien type
                $copiedPilote = new Pilote();

                // Copier les propriétés du pilote original
                $copiedPilote->setNom($originalPilote->getNom());
                $copiedPilote->setPrenom($originalPilote->getPrenom());
                $copiedPilote->setNumero($originalPilote->getNumero());
                $copiedPilote->setFonction($originalPilote->getFonction());

                // Conversion des objets DateTime en chaînes (format Y-m-d)
                $copiedPilote->setDatebirth($originalPilote->getDatebirth() ? $originalPilote->getDatebirth()->format('Y-m-d') : null);
                $copiedPilote->setFirstdate($originalPilote->getFirstdate() ? $originalPilote->getFirstdate()->format('Y-m-d') : null);
                $copiedPilote->setValidite($originalPilote->getValidite() ? $originalPilote->getValidite()->format('Y-m-d') : null);
                $copiedPilote->setDatequalif($originalPilote->getDatequalif() ? $originalPilote->getDatequalif()->format('Y-m-d') : null);
                $copiedPilote->setDatelangue($originalPilote->getDatelangue() ? $originalPilote->getDatelangue()->format('Y-m-d') : null);

                // Copier les autres propriétés
                $copiedPilote->setAvion($originalPilote->getAvion());
                $copiedPilote->setPrivilegefr($originalPilote->getPrivilegefr());
                $copiedPilote->setNationalite($originalPilote->getNationalite());
                $copiedPilote->setPays($originalPilote->getPays());
                $copiedPilote->setCompagnie($originalPilote->getCompagnie());
                $copiedPilote->setPrivilegeag($originalPilote->getPrivilegeag());
                $copiedPilote->setCreatedBy($originalPilote->getCreatedBy());

                // S'assurer que la copie garde l'ancien type
                $copiedPilote->setType($oldType);

                // Persister la copie dans la base de données
                $entityManager->persist($copiedPilote);
            }

            // Sauvegarder les modifications du pilote avec le nouveau type
            $entityManager->persist($pilote);
            $entityManager->flush();

            // Ajouter un message flash de succès
            $this->addFlash('success', 'Le pilote a été modifié avec succès et une copie de l\'ancien pilote a été créée.');

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

    #[Route('/pilote/update-statut-and-date/{id}', name: 'update_statut_and_date', methods: ['POST'])]
    public function updateStatutAndDate(Request $request, Pilote $pilote): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['statut'])) {
            // Mettre à jour le statut
            $pilote->setStatut($data['statut']);

            // Appliquer le service pour mettre à jour la firstdate
            $this->piloteStatusService->updatePiloteStatus($pilote);

            // Sauvegarder les modifications
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false, 'error' => 'Statut non fourni'], 400);
    }
}