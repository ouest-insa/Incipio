<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\User;

use App\Entity\User\User;
use App\Form\EventSubscriber\AddMembreFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * A rewrite of FOS\UserBundle\Form\Type\ProfileFormType because it can't handle parameters given through options.
 *
 * Class UserAdminType
 */
class UserAdminType extends AbstractType
{
    protected $class = User::class;

    protected $roles;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->roles = $options['roles'];
        $this->class = $options['user_class'];

        $subscriber = new AddMembreFieldSubscriber();
        $builder->addEventSubscriber($subscriber);

        $builder->add('enabled', CheckboxType::class, [
            'label' => 'Adresse email validé ?',
            'required' => false,
        ]);
        $builder->add('roles', ChoiceType::class, [
            'choices' => $this->refactorRoles($this->roles),
            'required' => false, 'label' => 'Roles', 'multiple' => true,
        ]);

        //from ProfileFormType
        $this->buildUserForm($builder, $options);

        $builder->add('current_password', PasswordType::class, [
            'label' => 'Mot de passe admin pour validation',
            'mapped' => false,
            'constraints' => new UserPassword(),
        ]);
        // end from
    }

    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    protected function buildUserForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, ['label' => 'form.username', 'translation_domain' => 'FOSUserBundle'])
            ->add('email', EmailType::class, ['label' => 'form.email', 'translation_domain' => 'FOSUserBundle']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
            'csrf_token_id' => 'profile',
            // BC for SF < 2.8
            'intention' => 'profile',
        ]);
        $resolver->setRequired(['user_class', 'roles']);
    }

    public function getName()
    {
        return 'user_useradmin';
    }

    private function refactorRoles($originRoles)
    {
        $roles = [];

        $rolesParent = array_keys($originRoles);
        foreach ($rolesParent as $roleParent) {
            if ('ROLE_SUPER_ADMIN' != $roleParent) {
                $roles[$roleParent] = $roleParent;
            }
        }

        return $roles;
    }
}
