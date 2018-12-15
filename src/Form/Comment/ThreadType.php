<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\CommentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThreadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('permalink')
            //->add('isCommentable')
            //->add('numComments')
            //->add('lastCommentAt')
            ->add('id')
            //->add('auteur')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\CommentBundle\Entity\Thread',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_commentbundle_threadtype';
    }
}
