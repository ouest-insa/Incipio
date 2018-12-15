<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\FormationBundle\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2ChoiceType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Mgate\FormationBundle\Entity\Formation;
use Mgate\PersonneBundle\Entity\PersonneRepository as PersonneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titre', TextType::class, [
            'label' => 'formation.titre',
            'translation_domain' => 'formation',
            'required' => true, ])
            ->add('description', TextareaType::class, [
                'label' => 'formation.description',
                'translation_domain' => 'formation',
                'required' => true,
                'attr' => ['cols' => '100%', 'rows' => 5], ])
            ->add('categorie', Select2ChoiceType::class, [
                'label' => 'formation.categories',
                'translation_domain' => 'formation',
                'multiple' => true,
                'choices' => array_flip(Formation::getCategoriesChoice()),
                'required' => false, ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'formation.date_debut',
                'translation_domain' => 'formation',
                'format' => 'd/MM/y - HH:mm',
                'required' => true,
                'widget' => 'choice', ])
            ->add('dateFin', DateTimeType::class, [
                'label' => 'formation.date_fin',
                'translation_domain' => 'formation',
                'format' => 'd/MM/y - HH:mm',
                'required' => true,
                'widget' => 'choice', ])
            ->add('mandat', IntegerType::class, [
                'label' => 'formation.mandat',
                'translation_domain' => 'formation',
                'required' => true, ])
            ->add('formateurs', CollectionType::class, [
                'label' => 'formation.formateurs',
                'translation_domain' => 'formation',
                'entry_type' => Select2EntityType::class,
                'entry_options' => ['label' => 'Suiveur de projet',
                    'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                    'choice_label' => 'prenomNom',
                    'query_builder' => function (PersonneRepository $pr) {
                        return $pr->getMembreOnly();
                    },
                    'required' => false, ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false, ])
            ->add('membresPresents', CollectionType::class, [
                'label' => 'formation.membres_present',
                'translation_domain' => 'formation',
                'entry_type' => Select2EntityType::class,
                'entry_options' => ['label' => 'Suiveur de projet',
                    'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                    'choice_label' => 'prenomNom',
                    'query_builder' => function (PersonneRepository $pr) {
                        return $pr->getMembreOnly();
                    },
                    'required' => false, ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false, ])
            ->add('docPath', TextType::class, [
                'label' => 'formation.lien_documents',
                'translation_domain' => 'formation',
                'required' => false, ]);
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_formulairetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\FormationBundle\Entity\Formation',
        ]);
    }
}
