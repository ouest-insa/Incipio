<?php

namespace N7consulting\RhBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetenceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description')
            ->add('nom')
            ->add('membres')
            ->add('etudes')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'N7consulting\RhBundle\Entity\Competence',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'N7consulting_rhbundle_competence';
    }
}
