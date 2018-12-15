<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\TresoBundle\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Mgate\PersonneBundle\Entity\PersonneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteDeFraisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mandat', IntegerType::class, ['label' => 'Mandat', 'required' => true])
                ->add('numero', IntegerType::class, ['label' => 'Numéro de la Note de Frais', 'required' => true])
                ->add('objet', TextareaType::class,
                    ['label' => 'Objet de la Note de Frais',
                        'required' => false,
                        'attr' => [
                            'cols' => '100%',
                            'rows' => 5, ],
                        ]
                    )
                ->add('details', CollectionType::class, [
                    'entry_type' => NoteDeFraisDetailType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false,
                ])
                ->add('demandeur', Select2EntityType::class, [
                      'label' => 'Demandeur',
                       'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                       'choice_label' => 'prenomNom',
                       'query_builder' => function (PersonneRepository $pr) {
                           return $pr->getMembreOnly();
                       },
                       'required' => true, ])
                ->add('date', DateType::class, ['label' => 'Date', 'required' => true, 'widget' => 'single_text']);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_tresobundle_notedefraistype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\TresoBundle\Entity\NoteDeFrais',
        ]);
    }
}
