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

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Mgate\PersonneBundle\Entity\PersonneRepository;
use Mgate\PersonneBundle\Form\Type\ProspectType;
use Mgate\SuiviBundle\Entity\Etude;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtudeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('knownProspect', CheckboxType::class, [
            'label' => 'suivi.etude_form.client_bdd',
            'translation_domain' => 'suivi',
            'required' => false,
        ])
            ->add('prospect', Select2EntityType::class, [
                'class' => 'Mgate\PersonneBundle\Entity\Prospect',
                'choice_label' => 'nom',
                'label' => 'suivi.etude_form.prospect_existant',
                'translation_domain' => 'suivi',
                'required' => false,
            ])
            ->add('newProspect', ProspectType::class, [
                'label' => 'suivi.etude_form.prospect_nouveau',
                'translation_domain' => 'suivi',
                'required' => false,
            ])
            ->add('nom', TextType::class, [
                'label' => 'suivi.etude_form.nom_interne',
                'translation_domain' => 'suivi',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'suivi.etude_form.presentation_interne',
                'translation_domain' => 'suivi',
                'attr' => ['cols' => '100%', 'rows' => 5],
                'required' => false,
            ])
            ->add('mandat', IntegerType::class, [
                'label' => 'suivi.etude_form.mandat',
                'translation_domain' => 'suivi',
            ])
            ->add('num', IntegerType::class, [
                'label' => 'suivi.etude_form.numero',
                'translation_domain' => 'suivi',
                'required' => false,
            ])
            ->add('confidentiel', CheckboxType::class, [
                'label' => 'suivi.etude_form.confidentialite',
                'translation_domain' => 'suivi',
                'attr' => ['title' => 'suivi.etude_form.confidentialite_tooltip'],
                'required' => false,
            ])
            ->add('suiveur', Select2EntityType::class, [
                'label' => 'suivi.etude_form.suiveur_projet',
                'translation_domain' => 'suivi',
                'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                'choice_label' => 'prenomNom',
                'query_builder' => function (PersonneRepository $pr) {
                    return $pr->getMembreOnly();
                },
                'required' => false,
            ])
            ->add('suiveurQualite', Select2EntityType::class, [
                'label' => 'suivi.etude_form.suiveur_qualite',
                'translation_domain' => 'suivi',
                'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                'choice_label' => 'prenomNom',
                'query_builder' => function (PersonneRepository $pr) {
                    return $pr->getMembreOnly();
                },
                'required' => false,
            ])
            ->add('domaineCompetence', Select2EntityType::class, [
                'class' => 'Mgate\SuiviBundle\Entity\DomaineCompetence',
                'choice_label' => 'nom',
                'label' => 'suivi.etude_form.domaine_competence',
                'translation_domain' => 'suivi',
                'required' => false,
            ])
            ->add('sourceDeProspection', ChoiceType::class, [
                'choices' => array_flip(Etude::getSourceDeProspectionChoice()),
                'label' => 'suivi.etude_form.source_prospection',
                'translation_domain' => 'suivi',
                'required' => false,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_etudetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\SuiviBundle\Entity\Etude',
        ]);
    }
}
