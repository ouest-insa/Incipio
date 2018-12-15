<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhasesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('phases', CollectionType::class, [
            'entry_type' => PhaseType::class,
            'entry_options' => ['etude' => $options['etude']],
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false, //indispensable cf doc
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_etudephasestype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\SuiviBundle\Entity\Etude',
            'etude' => null,
            ]);
    }
}
