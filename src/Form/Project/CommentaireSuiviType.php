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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireSuiviType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // builder add('stateDescription', 'textarea', array( 'label'=>'Objectif', 'required'=>false));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_commentairesuivitype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => 'Mgate\SuiviBundle\Entity\Etude',
            ]);
    }
}
