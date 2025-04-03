<?php

namespace App\Form;

use App\Entity\Movie;
use PhpParser\Node\Stmt\Label;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champs du formulaire

            ->add('titleMovie', null, [
                'label' => false,
                'attr' => [
                    'hidden' => 'hidden'
                ]
            ])

            ->add('idtmdb', null, [
                'label' => false,
                'attr' => [
                    'hidden' => 'hidden'
                ]
            ])


            // Bouton de validation du formulaire
            ->add('Save', SubmitType::class, [
                'label' => 'Enregistrer en BDD.',
                'attr' => [
                    'class' => 'save btn btn-primary'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}
