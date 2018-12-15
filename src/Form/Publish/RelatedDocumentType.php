<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PubliBundle\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatedDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['etude']) {
            $builder->add('etude', Select2EntityType::class, [
                'class' => 'Mgate\SuiviBundle\Entity\Etude',
                'choice_label' => 'nom',
                'required' => false,
                'label' => 'Document lié à l\'étude',
                'data' => $options['etude'],
                'attr' => ['style' => 'min-width: 300px'],
                'configs' => ['placeholder' => 'Sélectionnez une étude', 'allowClear' => true],
            ]);
        }
        if ($options['prospect']) {
            $builder->add('prospect', Select2EntityType::class, [
                'class' => 'Mgate\PersonneBundle\Entity\Prospect',
                'choice_label' => 'nom',
                'required' => false,
                'label' => 'Document lié au prospect',
                'attr' => ['style' => 'min-width: 300px'],
                'configs' => ['placeholder' => 'Sélectionnez un prospect', 'allowClear' => true],
            ]);
        }
        if ($options['formation']) {
            $builder->add('formation', Select2EntityType::class, [
                'class' => 'Mgate\FormationBundle\Entity\Formation',
                'choice_label' => 'titre',
                'required' => false,
                'label' => 'Document lié à la formation',
                'attr' => ['style' => 'min-width: 300px'],
                'configs' => ['placeholder' => 'Sélectionnez une formation', 'allowClear' => true],
            ]);
        }
        if ($options['etudiant'] || $options['etude']) {
            $builder->add('membre', Select2EntityType::class, [
                'label' => 'Document lié à l\'étudiant (optionnel)',
                'class' => 'Mgate\\PersonneBundle\\Entity\\Membre',
                'choice_label' => 'personne.prenomNom',
                'required' => false,
                'attr' => ['style' => 'min-width: 300px'],
                'configs' => ['placeholder' => 'Sélectionnez un étudiant', 'allowClear' => true], ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_categoriedocumenttype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Mgate\PubliBundle\Entity\RelatedDocument',
            'etude' => null,
            'etudiant' => null,
            'prospect' => null,
            'formation' => null,
        ]);
    }
}
