<?php
// src/Validator/UniquePiloteNumeroValidator.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Repository\PiloteRepository;

class UniquePiloteNumeroValidator extends ConstraintValidator
{
    private $piloteRepository;

    public function __construct(PiloteRepository $piloteRepository)
    {
        $this->piloteRepository = $piloteRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;  // Si la valeur est null, on ne valide pas
        }

        // Récupérer l'entité (Pilote) via le root du formulaire
        $currentPilote = $this->context->getRoot()->getData();

        // Assurez-vous que $currentPilote est bien une instance de Pilote
        if (!$currentPilote instanceof \App\Entity\Pilote) {
            return; // Si ce n'est pas un pilote, on ne fait rien
        }

        // Cherche un autre pilote avec le même numéro ET le même type dans la base de données
        $existingPilote = $this->piloteRepository->findOneBy([
            'numero' => $value,
            'type' => $currentPilote->getType()  // On vérifie également le type
        ]);

        // Si un autre pilote avec ce numéro et type existe, et ce n'est pas le pilote en cours de modification, on ajoute une violation
        if ($existingPilote && $existingPilote->getId() !== $currentPilote->getId()) {
            // Le numéro est déjà utilisé par un autre pilote du même type
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
