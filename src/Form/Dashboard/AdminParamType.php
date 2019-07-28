<?php

namespace App\Form\Dashboard;

use App\Entity\Dashboard\AdminParam;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminParamType extends AbstractType
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = $this->em->getRepository(AdminParam::class)->findBy([], ['priority' => 'desc']);

        foreach ($fields as $field) {
            /* @var $field AdminParam */
            $builder->add($field->getName(), $this->chooseType($field->getParamType()),
                ['required' => $field->getRequired(),
                 'label' => $field->getParamLabel(),
                 'attr' => ['tooltip' => $field->getParamDescription()],
                ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'dashboard_adminparam';
    }

    /**
     * Returns the class associated with form type string.
     *
     * @param $formType string the string representing the form type
     *
     * @return mixed
     */
    private function chooseType($formType)
    {
        if ('string' === $formType) {
            return TextType::class;
        } elseif ('integer' === $formType) {
            return IntegerType::class;
        } elseif ('number' === $formType) {
            return NumberType::class;
        } elseif ('url' === $formType) {
            return UrlType::class;
        } else {
            throw new \LogicException('Type ' . $formType . ' is invalid.');
        }
    }
}
