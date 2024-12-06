<?php

namespace App\Controller;

use App\Entity\Directeur;
use App\Entity\Ministere;
use App\Entity\Pilote;
use App\Form\PiloteType;
use App\Repository\PiloteRepository;
use App\Repository\ValidationHistoriqueRepository;
use App\Service\PdfGenerator;
use App\Service\PiloteStatusService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

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
        $atplPilotes = $piloteRepository->findBy(['type' => false]);

        return $this->render('pilote/index_atpl.html.twig', [
            'pilotes' => $atplPilotes,
        ]);
    }

    #[Route('/CPL', name: 'app_pilote_cpl', methods: ['GET'])]
    public function cpl(PiloteRepository $piloteRepository): Response
    {
        $cplPilotes = $piloteRepository->findBy(['type' => true]);

        return $this->render('pilote/index_cpl.html.twig', [
            'pilotes' => $cplPilotes,
        ]);
    }

    #[Route('/DOUBLE', name: 'app_pilote_double', methods: ['GET'])]
    public function double(PiloteRepository $piloteRepository): Response
    {
        $doublePilotes = $piloteRepository->findBy(['type' => null]);

        return $this->render('pilote/index_double.html.twig', [
            'pilotes' => $doublePilotes,
        ]);
    }

    #[Route('/new', name: 'app_pilote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $pilote = new Pilote();

        $form = $this->createForm(PiloteType::class, $pilote);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if ($user) {
                $pilote->setCreatedBy($user);
            } else {
                throw $this->createAccessDeniedException('Utilisateur non connecté.');
            }

            $entityManager->persist($pilote);
            $entityManager->flush();

            $session->getFlashBag()->add('success', 'Pilote ajouté avec succès par ' . $user->getFirstname() . ' ' . $user->getLastname());

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
        $originalPilote = clone $pilote;

        $form = $this->createForm(PiloteType::class, $pilote, [
            'is_update' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldType = $originalPilote->getType();
            $newType = $pilote->getType();

            if ($oldType !== $newType) {
                $pilote->setType($oldType);

                $copiedPilote = new Pilote();
                $copiedPilote->setNom($originalPilote->getNom());
                $copiedPilote->setPrenom($originalPilote->getPrenom());
                $copiedPilote->setNumero($originalPilote->getNumero());
                $copiedPilote->setFonction($originalPilote->getFonction());
                $copiedPilote->setDatebirth($originalPilote->getDatebirth() ? $originalPilote->getDatebirth()->format('Y-m-d') : null);
                $copiedPilote->setFirstdate($originalPilote->getFirstdate() ? $originalPilote->getFirstdate()->format('Y-m-d') : null);
                $copiedPilote->setValidite($originalPilote->getValidite() ? $originalPilote->getValidite()->format('Y-m-d') : null);
                $copiedPilote->setDatequalif($originalPilote->getDatequalif() ? $originalPilote->getDatequalif()->format('Y-m-d') : null);
                $copiedPilote->setDatelangue($originalPilote->getDatelangue() ? $originalPilote->getDatelangue()->format('Y-m-d') : null);
                $copiedPilote->setAvion($originalPilote->getAvion());
                $copiedPilote->setPrivilegefr($originalPilote->getPrivilegefr());
                $copiedPilote->setNationalite($originalPilote->getNationalite());
                $copiedPilote->setPays($originalPilote->getPays());
                $copiedPilote->setCompagnie($originalPilote->getCompagnie());
                $copiedPilote->setPrivilegeag($originalPilote->getPrivilegeag());
                $copiedPilote->setCreatedBy($originalPilote->getCreatedBy());
                $copiedPilote->setType($newType);
                $copiedPilote->setValidationHistoriques(new ArrayCollection());

                $entityManager->persist($copiedPilote);
                $entityManager->persist($pilote);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le pilote a été modifié avec succès et une copie de l\'ancien pilote a été créée.');

            return $this->redirectToRoute('app_pilote_edit', ['id' => $pilote->getId()]);
        }

        return $this->render('pilote/edit.html.twig', [
            'pilote' => $pilote,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_pilote_delete', methods: ['POST'])]
    public function delete(Request $request, Pilote $pilote, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $pilote->getId(), $request->request->get('_token'))) {
            $piloteType = $pilote->getType();
            $entityManager->remove($pilote);
            $entityManager->flush();

            if ($piloteType === false) {
                return $this->redirectToRoute('app_pilote_atpl', [], Response::HTTP_SEE_OTHER);
            } elseif ($piloteType === true) {
                return $this->redirectToRoute('app_pilote_cpl', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_pilote_double', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('app_pilote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/generate-pdf/{id}', name: 'pilote_generate_pdf')]
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


    #[Route('/update-statut-and-date/{id}', name: 'update_statut_and_date', methods: ['POST'])]
    public function updateStatutAndDate(Request $request, Pilote $pilote): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['statut'])) {
            $pilote->setStatut($data['statut']);
            $this->piloteStatusService->updatePiloteStatus($pilote);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false, 'error' => 'Statut non fourni'], 400);
    }

    #[Route('/{id}/historique', name: 'pilote_historique', methods: ['GET'])]
    public function afficherHistorique(Pilote $pilote, ValidationHistoriqueRepository $historiqueRepository): Response
    {
        $historique = $historiqueRepository->findBy(['pilote' => $pilote]);

        return $this->render('pilote/historique.html.twig', [
            'pilote' => $pilote,
            'historique' => $historique
        ]);
    }
}
