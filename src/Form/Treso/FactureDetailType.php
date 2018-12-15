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

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactureDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description', TextareaType::class,
                    ['label' => 'Description de la dépense',
                        'required' => false,
                        'attr' => [
                            'cols' => '100%',
                            'rows' => 2, ],
                        ]
                    )
                ->add('montantHT', MoneyType::class, ['label' => 'Prix H.T.', 'required' => false])
                ->add('tauxTVA', NumberType::class, ['label' => 'Taux TVA (%)', 'required' => false])
                ->add('compte', Select2EntityType::class, [
                        'class' => 'Mgate\TresoBundle\Entity\Compte',
                        'choice_label' => 'libelle',
                        'required' => false,
                        'label' => 'Catégorie',
                        'configs' => ['placeholder' => 'Sélectionnez une catégorie', 'allowClear' => true],
                        ]);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_tresobundle_facturedetailtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\TresoBundle\Entity\FactureDetail',
        ]);
    }
}
