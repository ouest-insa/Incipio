<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Listener responsible to send a mail to admin at each user registration.
 */
class RegistrationListener implements EventSubscriberInterface
{
    private $mailer;

    private $templating;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onRegistrationConfirmed',
        ];
    }

    // Prévenir lorsque quelqu'un valide compte
    public function onRegistrationConfirmed(FilterUserResponseEvent $event)
    {
        $message = new \Swift_Message();
        $message->setSubject('Jeyser CRM : Nouvel utilisateur ' . $event->getUser()->getUsername())
            ->setFrom(getenv('TECHNICAL_FROM'))
            ->setTo(getenv('TECHNICAL_TO'))
            ->setBody($this->templating->render('bundles/FOSUserBundle/Default/alert-email.html.twig',
                ['username' => $event->getUser()->getUsername(), 'email' => $event->getUser()->getEmail()]),
                'text/html');
        $this->mailer->send($message);
    }
}
