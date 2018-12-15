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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class,
                    ['label' => 'Libellé du compte',
                        'required' => true, ]
                    )
                ->add('numero', TextType::class, ['label' => 'Numéro de compte', 'required' => true, 'attr' => ['maxlength' => 6]])
                ->add('categorie', CheckboxType::class, ['label' => 'Est utilisé comme catégorie ? ', 'required' => false]);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_tresobundle_comptetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\TresoBundle\Entity\Compte',
        ]);
    }
}
