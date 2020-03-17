<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Treso;

use App\Entity\Treso\NoteDeFraisDetail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteDeFraisDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prixHT', MoneyType::class, [
                'label' => 'Prix H.T.',
                'required' => false
            ])
            ->add('tauxTVA', NumberType::class, [
                'label' => 'Taux TVA (%)',
                'empty_data' => 20,
                'required' => false
            ])
            ->add('kilometrage', IntegerType::class, [
                'label' => 'Nombre de Kilomètre',
                'required' => false
            ])
            ->add('tauxKm', IntegerType::class, [
                'label' => 'Prix au kilomètre (en cts)',
                'required' => false
            ])
            ->add('peageHT', MoneyType::class, [
                'label' => 'Montant de péages HT',
                'required' => false
            ])
            ->add('tvaPeages', NumberType::class, [
                'label' => 'Taux TVA péages (%)',
                'empty_data' => 20,
                'required' => false
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de la note',
                'choices' => array_flip(NoteDeFraisDetail::getTypeChoices()),
            ]);
        /*
            ->add('compte', Select2EntityType::class, [
                'class' => Compte::class,
                'choice_label' => 'libelle',
                'label' => 'Catégorie',
                'configs' => [
                    'placeholder' => 'Sélectionnez une catégorie',
                    'allowClear' => true
                ],
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de la dépense',
                'attr' => [
                    'cols' => '100%',
                    'rows' => 5,
                ],
                'required' => true
            ]);
        */
    }

    public function getBlockPrefix()
    {
        return 'treso_notedefraisdetailtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NoteDeFraisDetail::class,
        ]);
    }
}
