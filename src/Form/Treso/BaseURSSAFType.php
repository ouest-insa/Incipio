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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseURSSAFType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('baseURSSAF', MoneyType::class, ['label' => 'Base en Euro', 'required' => true])
            ->add('dateDebut', DateType::class, ['label' => 'Applicable du', 'required' => true, 'widget' => 'single_text'])
            ->add('dateFin', DateType::class, ['label' => 'Applicable au', 'required' => true, 'widget' => 'single_text']);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_tresobundle_baseurssaftype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\TresoBundle\Entity\BaseURSSAF',
        ]);
    }
}
