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
use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Entity\PhaseRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionType extends DocTypeType
{
    protected $etude;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!isset($options['etude']) || !($options['etude'] instanceof Etude)) {
            throw new \LogicException('A MissionsType can\'t be build without associated Etude object.');
        }
        $this->etude = $options['etude'];

        $builder
            ->add('intervenant', Select2EntityType::class, [
                'class' => 'Mgate\\PersonneBundle\\Entity\\Membre',
                'choice_label' => 'personne.prenomNom',
                'label' => 'Intervenant',
                //'query_builder' => function(PersonneRepository $pr) { return $pr->getMembreOnly(); },
                'required' => true,
            ])
            ->add('debutOm', Datetype::class, ['label' => 'Début du Récapitulatif de Mission',
                'required' => true, 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', ])
            ->add('finOm', DateType::class, ['label' => 'Fin du Récapitulatif de Mission',
                'required' => true, 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', ])
            ->add('pourcentageJunior', PercentType::class, ['label' => 'Pourcentage junior', 'required' => true, 'scale' => 2])
            ->add('referentTechnique', Select2EntityType::class, [
                'class' => 'Mgate\\PersonneBundle\\Entity\\Membre',
                'choice_label' => 'personne.prenomNom',
                'label' => 'Référent Technique',
                'required' => false,
            ])
            ->add('phases', EntityType::class, [
                'class' => 'Mgate\SuiviBundle\Entity\Phase',
                'query_builder' => function (PhaseRepository $pr) {
                    return $pr->getByEtudeQuery($this->etude);
                },
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'attr' => ['class' => 'select2-multiple'],
            ])
            ->add('repartitionsJEH', CollectionType::class, [
                'entry_type' => RepartitionJEHType::class,
                'entry_options' => [
                    'data_class' => 'Mgate\SuiviBundle\Entity\RepartitionJEH',
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ]);

        //->add('avancement','integer',array('label'=>'Avancement en %'))
        //->add('rapportDemande','checkbox', array('label'=>'Rapport pédagogique demandé','required'=>false))
        //->add('rapportRelu','checkbox', array('label'=>'Rapport pédagogique relu','required'=>false))
        //->add('remunere','checkbox', array('label'=>'Intervenant rémunéré','required'=>false));

        //->add('mission', new DocTypeType('mission'), array('label'=>' '));
        DocTypeType::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'Mgate_suivibundle_mssiontype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\SuiviBundle\Entity\Mission',
        ]);
        $resolver->setRequired(['etude']);
        $resolver->addAllowedTypes('etude', 'Mgate\SuiviBundle\Entity\Etude');
    }
}
