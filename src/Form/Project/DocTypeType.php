<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Project;

use App\Entity\Personne\Personne;
use App\Entity\Project\Av;
use App\Entity\Project\AvMission;
use App\Entity\Project\DocType;
use App\Entity\Project\Mission;
use App\Form\Personne\EmployeType;
use App\Repository\Personne\PersonneRepository;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
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
             'class' => Personne::class,
             'choice_label' => 'prenomNom',
             'query_builder' => function (PersonneRepository $pr) {
                 return $pr->getMembresByPoste('president%');
             },
             'required' => true,
            ]);

        // Si le document n'est ni une FactureVente ni un RM
        if (Mission::class != $options['data_class'] &&
            AvMission::class != $options['data_class']
        ) {
            // le signataire 2 est l'intervenant

            $pro = $options['prospect'];
            if (Av::class != $options['data_class']) {
                $builder->add('knownSignataire2', CheckboxType::class,
                    [
                        'required' => false,
                        'label' => 'Le signataire client existe-t-il déjà dans la base de donnée ?',
                    ])
                    ->add('newSignataire2', EmployeType::class,
                        ['label' => 'Nouveau signataire ' . $pro->getNom(),
                         'required' => false,
                         'signataire' => true,
                         'mini' => true,
                        ]
                    );
            }

            $builder->add('signataire2', Select2EntityType::class, [
                'class' => Personne::class,
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
             'attr' => ['autocomplete' => 'off'],
            ]);
    }

    public function getBlockPrefix()
    {
        return 'project_doctypetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DocType::class,
            'prospect' => '',
        ]);
    }
}
