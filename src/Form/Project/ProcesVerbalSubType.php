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

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcesVerbalSubType extends DocTypeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $phaseNum = $options['phases'];
        if ('pvi' == $options['type']) {
            $builder->add('phaseID', IntegerType::class, ['label' => 'Phases concernées', 'required' => false, 'attr' => ['min' => '1', 'max' => $phaseNum]]);
        }

        DocTypeType::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'Mgate_suivibundle_procesverbalsubtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\SuiviBundle\Entity\ProcesVerbal',
            'type' => null,
            'prospect' => null,
            'phases' => null,
        ]);
    }
}
