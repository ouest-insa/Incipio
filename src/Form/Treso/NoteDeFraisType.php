<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Treso;

use App\Entity\Personne\Personne;
use App\Entity\Treso\NoteDeFrais;
use App\Repository\Personne\PersonneRepository;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteDeFraisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) //https://openclassrooms.com/forum/sujet/symfony-3-form-dynamique-selon-choicetype
    {
        $builder

            ->add('mandat', IntegerType::class, [
                'label' => 'Mandat',
                'required' => true,
            ])

            ->add('numero', TextType::class, [
                'label' => 'Numéro de la Note de Frais',
                'required' => true,
            ])

            ->add('objet', TextareaType::class, [
                'label' => 'Objet de la Note de Frais',
                 'attr' => [
                     'cols' => '100%',
                     'rows' => 5,
                 ],
                'required' => true,
            ])

            ->add('adressedTo', ChoiceType::class, [
                'label' => 'Rôle du demandeur',
                'choices' => [
                    'Membre' => NoteDeFrais::NF_TO_NORMAL,
                    'Trésorier' => NoteDeFrais::NF_TO_TRESORIER,
                    'Président' => NoteDeFrais::NF_TO_PRESIDENT
                ],
                'required' => true
            ])

            ->add('demandeur', Select2EntityType::class, [
                'label' => 'Demandeur',
                'class' => Personne::class,
                'choice_label' => 'prenomNom',
                'query_builder' => function (PersonneRepository $pr) {
                    return $pr->getMembreOnly();
                },
                'required' => true,
            ])

            ->add('details', CollectionType::class, [
                'entry_type' => NoteDeFraisDetailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ])

            ->add('date', DateType::class, [
                'label' => 'Date',
                'required' => true,
                'widget' => 'single_text',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'treso_notedefraistype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NoteDeFrais::class,
        ]);
    }
}