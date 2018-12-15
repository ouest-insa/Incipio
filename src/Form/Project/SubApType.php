<?php

namespace Mgate\SuiviBundle\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Mgate\PersonneBundle\Entity\PersonneRepository;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubApType extends DocTypeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contactMgate', Select2EntityType::class, ['label' => "'En cas d’absence ou de problème, il est également possible de joindre ...' ex: Vice-Président",
            'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
            'choice_label' => 'prenomNom',
            'attr' => ['title' => "Dans l'AP: 'En cas d’absence ou de problème, il est également possible de joindre le ...'"],
            'query_builder' => function (PersonneRepository $pr) {
                return $pr->getMembresByPoste('%vice-president%');
            },
            'required' => true, ]);
        DocTypeType::buildForm($builder, $options);
        $builder->add('nbrDev', IntegerType::class,
            ['label' => 'Nombre d\'intervenants estimé',
                'required' => false,
                'attr' => ['title' => 'Mettre 0 pour ne pas afficher la phrase indiquant le nombre d\'intervenant'], ]
        );
    }

    public function getName()
    {
        return 'Mgate_suivibundle_subaptype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\SuiviBundle\Entity\Ap',
            'prospect' => '',
        ]);
    }
}
