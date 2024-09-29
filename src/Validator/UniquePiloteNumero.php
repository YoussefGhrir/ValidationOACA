<?php
// src/Validator/UniquePiloteNumero.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniquePiloteNumero extends Constraint
{
    public $message = 'Le numéro "{{ value }}" est déjà utilisé pour un autre pilote.';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
