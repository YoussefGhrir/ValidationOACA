<?php
// src/Validator/UniquePiloteNumeroValidator.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;
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
            return;
        }

        // Vérifie si un pilote avec ce numéro existe déjà
        $existingPilote = $this->piloteRepository->findOneBy(['numero' => $value]);

        if ($existingPilote) {
            // Ajoute une violation si le numéro existe déjà
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
