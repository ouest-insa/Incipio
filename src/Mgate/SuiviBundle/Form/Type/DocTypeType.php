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

use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Mgate\PersonneBundle\Entity\PersonneRepository;
use Mgate\PersonneBundle\Form\Type\EmployeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Version du document
        $builder->add('version', IntegerType::class, ['label' => 'Version du document']);

        $builder->add('signataire1', Select2EntityType::class,
            ['label' => 'Signataire Junior',
                'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                'choice_label' => 'prenomNom',
                'query_builder' => function (PersonneRepository $pr) {
                    return $pr->getMembresByPoste('president%');
                },
                'required' => true, ]);

        // Si le document n'est ni une FactureVente ni un RM
        if ('Mgate\SuiviBundle\Entity\Mission' != $options['data_class'] &&
            'Mgate\SuiviBundle\Entity\AvMission' != $options['data_class']) {
            // le signataire 2 est l'intervenant

            $pro = $options['prospect'];
            if ('Mgate\SuiviBundle\Entity\Av' != $options['data_class']) {
                $builder->add('knownSignataire2', CheckboxType::class,
                    [
                        'required' => false,
                        'label' => 'Le signataire client existe-t-il déjà dans la base de donnée ?',
                    ])
                    ->add('newSignataire2', EmployeType::class,
                        ['label' => 'Nouveau signataire ' . $pro->getNom(),
                            'required' => false,
                            'signataire' => true,
                            'mini' => true, ]
                    );
            }

            $builder->add('signataire2', Select2EntityType::class, [
                'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                'choice_label' => 'prenomNom',
                'label' => 'Signataire ' . $pro->getNom(),
                'query_builder' => function (PersonneRepository $pr) use ($pro) {
                    return $pr->getEmployeOnly($pro);
                },
                'required' => false,
            ]);
        }

        $builder->add('dateSignature', DateType::class,
            ['label' => 'Date de Signature du document',
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'attr' => ['autocomplete' => 'off']]);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_doctypetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\SuiviBundle\Entity\DocType',
            'prospect' => '',
        ]);
    }
}
