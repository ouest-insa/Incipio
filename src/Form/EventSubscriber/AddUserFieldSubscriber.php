<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle\Form\EventListener;

use Mgate\UserBundle\Entity\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Where is that stuff used ? Doesn't seem to be.
 *
 * Class AddUserFieldSubscriber
 */
class AddUserFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        // During form creation setData() is called with null as an argument
        // by the FormBuilder constructor. You're only concerned with when
        // setData is called with an actual Entity object in it (whether new
        // or fetched with Doctrine). This if statement lets you skip right
        // over the null condition.

        $personne = $data;
        $form->add('user', 'entity',
            ['label' => "Séléctionner un compte d'utilisateur associé s'il existe déjà",
                'class' => 'Mgate\\UserBundle\\Entity\\User',
                'property' => 'username',
                'required' => false,
                'query_builder' => function (UserRepository $ur) use ($personne) {
                    return $ur->getNotPersonne($personne);
                },
            ]);
    }
}
