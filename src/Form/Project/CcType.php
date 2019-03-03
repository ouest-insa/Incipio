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

use App\Entity\Project\Etude;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CcType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cc', SubCcType::class, ['label' => ' ', 'prospect' => $options['prospect']])
            ->add('acompte', CheckboxType::class, ['label' => 'Acompte', 'required' => false])
            ->add('pourcentageAcompte', PercentType::class, ['label' => 'Pourcentage acompte', 'required' => false]);
    }

    public function getBlockPrefix()
    {
        return 'project_cctype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Etude::class,
            'prospect' => '',
        ]);
    }
}
