<?php
namespace App\Form;

use App\Entity\Avion;
use App\Entity\Compagnie;
use App\Entity\Pilote;
use App\Validator\UniquePiloteNumero;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
// Import DateTime constraint

class PiloteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isUpdate = $options['is_update'];

        // Définir les contraintes du champ 'numero'
        $constraints = [new NotBlank()];

        // Appliquer la contrainte UniquePiloteNumero pour la création et la mise à jour (en vérifiant correctement l'unicité)
        $constraints[] = new UniquePiloteNumero(); // Ajoute la contrainte d'unicité

        $dateConstraints = [
            new NotBlank(),
            new DateTime(['format' => 'Y-m-d', 'message' => 'Veuillez entrer une date valide (YYYY-MM-DD).'])
        ];

        $builder
            ->add('nom')
            ->add('prenom')
            ->add('datelicence', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Validité de Licence',
                'constraints' => $dateConstraints,
            ])
            ->add('datebirth', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Date de naissance',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de naissance est obligatoire.',
                    ]),
                    new Assert\Date([
                        'message' => 'Veuillez entrer une date valide au format YYYY-MM-DD.',
                    ]),
                    new Assert\Callback([
                        'callback' => function (?string $dateOfBirth, ExecutionContextInterface $context): void {
                            if ($dateOfBirth === null) {
                                return;
                            }

                            $date = \DateTime::createFromFormat('Y-m-d', $dateOfBirth);
                            if (!$date) {
                                $context->buildViolation('La date de naissance n\'est pas valide.')
                                    ->addViolation();
                                return;
                            }

                            $today = new \DateTimeImmutable();
                            $maxAgeDate = $today->modify('-65 years');

                            if ($date < $maxAgeDate) {
                                $context->buildViolation('L\'âge maximal d\'un pilote est de 65 ans.')
                                    ->addViolation();
                            }
                        },
                    ]),
                ],
            ])
            ->add('numero', TextType::class, [
                'label' => 'Numéro',
                'constraints' => $constraints,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('firstdate', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Date de début',

                'constraints' => $dateConstraints,
            ])
            ->add('validite', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Date de validité',
                'data' => (new \DateTime())->format('Y-m-d'), // Conversion en chaîne de caractères
                'constraints' => $dateConstraints,
            ])
            ->add('datequalif', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Date Qualification',
                'constraints' => $dateConstraints,
            ])
            ->add('datelangue', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Date de langue',
                'constraints' => $dateConstraints,
            ])
            ->add('pays')
            ->add('nationalite')
            ->add('fonction', ChoiceType::class, [
                'choices' => [
                    '1er pilote / PIC' => '1er pilote / PIC',
                    '2ème pilote / Co-pilot' => '2ème pilote / Co-pilot',
                    'Double / Dual' => 'Double / Dual',
                ],
                'constraints' => [
                    new Choice([
                        'choices' => ['1er pilote / PIC', '2ème pilote / Co-pilot', 'Double / Dual'],
                        'message' => 'Veuillez choisir une option valide.'
                    ])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'ATPL' => false,  // false pour "ATPL"
                    'CPL' => true,    // true pour "CPL"
                    'Double' => null, // null pour "Double"
                ],
                'expanded' => true,  // Utiliser des boutons radio
                'multiple' => false, // Pas de sélection multiple
                'data' => $options['data']->getType(),  // Pré-sélectionner selon la valeur actuelle du pilote
            ])
            ->add('compagnie', EntityType::class, [
                'class' => Compagnie::class,
                'choice_label' => 'nom',
                'label' => 'Compagnie',
                'placeholder' => 'Choisissez une compagnie',
                'required' => false,  // Optionnel
            ])
            ->add('avion', EntityType::class, [
                'class' => Avion::class,
                'choice_label' => 'nom',
                'label' => 'Avion',
                'placeholder' => 'Choisissez un avion',
                'required' => false,  // Optionnel
            ])
            ->add('privilegefr', TextType::class, [
                'label' => 'Privilège Français',
                'required' => false, // Le champ est optionnel
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Privilège Français',
                ],
            ])
            ->add('privilegeag', TextType::class, [
                'label' => 'Privilège Anglais',
                'required' => false, // Le champ est optionnel
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Privilège Anglais',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pilote::class,
            'default_type' => null, // Aucune valeur par défaut
            'is_update' => false,
        ]);
    }
}