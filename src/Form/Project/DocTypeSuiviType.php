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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocTypeSuiviType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('redige', CheckboxType::class, ['label' => 'Est-ce que le document est rédigé ?', 'required' => false])
            ->add('relu', CheckboxType::class, ['label' => 'Est-ce que le document est relu ?', 'required' => false])
            ->add('envoye', CheckboxType::class, ['label' => 'Est-ce que le document est envoyé ?', 'required' => false])
            ->add('receptionne', CheckboxType::class, ['label' => 'Est-ce que le document est réceptionné ?', 'required' => false]);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_doctypetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => 'Mgate\SuiviBundle\Entity\DocType',
            ]);
    }
}
