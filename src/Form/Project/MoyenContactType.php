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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoyenContactType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'mail' => 'Mail',
                'telephone' => 'Téléphone',
                'physique' => 'Physique',
                'lettre' => 'Lettre',
            ],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'moyenContact';
    }
}
