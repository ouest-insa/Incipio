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
use Mgate\TresoBundle\Entity\NoteDeFraisDetail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteDeFraisDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description', TextareaType::class,
                    ['label' => 'Description de la dépense',
                        'required' => true,
                        'attr' => [
                            'cols' => '100%',
                            'rows' => 5, ],
                        ]
                    )
                ->add('prixHT', MoneyType::class, ['label' => 'Prix H.T.', 'required' => false])
                ->add('tauxTVA', NumberType::class, ['label' => 'Taux TVA (%)', 'required' => false])
                ->add('kilometrage', IntegerType::class, ['label' => 'Nombre de Kilomètre', 'required' => false])
                ->add('tauxKm', IntegerType::class, ['label' => 'Prix au kilomètre (en cts)', 'required' => false])
                ->add('type', ChoiceType::class, ['choices' => array_flip(NoteDeFraisDetail::getTypeChoices()), 'required' => true])
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
        return 'Mgate_tresobundle_notedefraisdetailtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\TresoBundle\Entity\NoteDeFraisDetail',
        ]);
    }
}
