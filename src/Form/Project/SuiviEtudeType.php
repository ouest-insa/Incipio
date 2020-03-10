<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Project;

use App\Entity\Project\Ap;
use App\Entity\Project\Cc;
use App\Entity\Project\Ce;
use App\Entity\Project\Etude;
use App\Entity\Project\ProcesVerbal;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType as GenemuDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuiviEtudeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('stateID', ChoiceType::class,
            ['choices' => array_flip(Etude::ETUDE_STATE_ARRAY),
             'translation_domain' => 'project',
             'label' => 'Etat de l\'Étude',
             'required' => true,
            ])
            ->add('auditDate', GenemuDateType::class,
                ['label' => 'Audité le', 'format' => 'd/MM/y', 'required' => false, 'widget' => 'single_text'])
            ->add('auditType', AuditType::class,
                ['label' => 'Type d\'audit', 'required' => false, 'choice_label' => function ($var) {
                    return $var;
                },
                ])
            ->add('stateDescription', TextareaType::class,
                ['label' => 'suivi.problemes', 'translation_domain' => 'project', 'required' => false, 'attr' => ['cols' => '100%', 'rows' => 5]])
            ->add('ap', DocTypeSuiviType::class, ['label' => 'Avant-Projet', 'data_class' => Ap::class])
            ->add('cc', DocTypeSuiviType::class, ['label' => 'Convention Client', 'data_class' => Cc::class])
            ->add('ce', DocTypeSuiviType::class, ['label' => 'Convention Etude', 'data_class' => Ce::class]);

        $builder->add('missions', CollectionType::class, [
            'entry_type' => DocTypeSuiviType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false, //indispensable cf doc
        ]);

        $builder->add('pvis', CollectionType::class, [
            'entry_type' => DocTypeSuiviType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false, //indispensable cf doc
        ]);
        $builder->add('avs', CollectionType::class, [
                'entry_type' => DocTypeSuiviType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false, //indispensable cf doc
            ]
        );
        $builder->add('pvr', DocTypeSuiviType::class, ['label' => 'PVR', 'data_class' => ProcesVerbal::class]);
    }

    public function getBlockPrefix()
    {
        return 'project_suivietudetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Etude::class,
        ]);
    }
}
