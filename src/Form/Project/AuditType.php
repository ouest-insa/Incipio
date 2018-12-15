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

class AuditType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                '0' => 'Non audité',
                '1' => 'Exhaustive',
                '2' => 'Déontologique',
            ],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'auditType';
    }
}
