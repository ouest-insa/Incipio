<?php

namespace App\Form\Project;

use App\Entity\Project\Etude;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$builder
            ->add('version')
            ->add('redige')
            ->add('relu')
            ->add('spt1')
            ->add('spt2')
            ->add('dateSignature')
            ->add('envoye')
            ->add('receptionne')
            ->add('generer')
            ->add('thread')
            ->add('signataire1')
            ->add('signataire2')
            ->add('etude')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Etude::class,
            'prospect' => '',
        ]);
    }
}
