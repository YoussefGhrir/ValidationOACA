<?php
namespace App\Form;

use App\Entity\Avion;
use App\Entity\Compagnie;
use App\Entity\Pilote;
use App\Validator\UniquePiloteNumero;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\DateTime; // Import DateTime constraint

class PiloteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer l'option 'is_update'
        $isUpdate = $options['is_update'];

        // Définir les contraintes pour le champ 'numero'
        $constraints = [
            new NotBlank(),
        ];

        // Appliquer la contrainte UniquePiloteNumero seulement si ce n'est pas une mise à jour
        if (!$isUpdate) {
            $constraints[] = new UniquePiloteNumero(); // Contrainte d'unicité appliquée uniquement lors de la création
        }
        $dateConstraints = [
            new NotBlank(),
            new DateTime(['format' => 'Y-m-d', 'message' => 'Veuillez entrer une date valide (YYYY-MM-DD).'])
        ];

        $builder
            ->add('nom')
            ->add('prenom')
            ->add('datebirth', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Date de naissance',
                'constraints' => $dateConstraints,
            ])
            ->add('numero', TextType::class, [
                'constraints' => $constraints, // Contrainte d'unicité appliquée seulement lors de la création
                'label' => 'Numéro',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'inputmode' => 'numeric',
                    'autocomplete' => 'off',
                    'spellcheck' => 'false',
                ],
            ])
            ->add('firstdate', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Date de début',
                'data' => (new \DateTime())->format('Y-m-d'), // Conversion en chaîne de caractères
                'constraints' => $dateConstraints,
            ])
            ->add('validite', TextType::class, [
                'attr' => [
                    'class' => 'form-control datepicker form-control-custom',
                    'placeholder' => 'YYYY-MM-DD'
                ],
                'label' => 'Date de validité',
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
