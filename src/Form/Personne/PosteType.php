<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PosteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('intitule', TextType::class, ['required' => true])
                ->add('description', TextType::class, ['required' => true])
        ;
    }

    public function getBlockPrefix()
    {
        return 'Mgate_personnebundle_posteetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\PersonneBundle\Entity\Poste',
        ]);
    }
}
