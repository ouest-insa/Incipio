<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\UserBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener responsible to send a mail to admin at each user registration.
 */
class RegistrationListener implements EventSubscriberInterface
{
    private $mailer;

    private $templating;

    private $mail_from;

    private $mail_to;

    public function __construct(\Swift_Mailer $mailer, $templating, $mail_from, $mail_to)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->mail_from = $mail_from;
        $this->mail_to = $mail_to;
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
        $message = \Swift_Message::newInstance()
            ->setSubject('Jeyser CRM : Nouvel utilisateur ' . $event->getUser()->getUsername())
            ->setFrom($this->mail_from)
            ->setTo($this->mail_to)
            ->setBody($this->templating->render('MgateUserBundle:Default:alert-email.html.twig',
                                        ['username' => $event->getUser()->getUsername(), 'email' => $event->getUser()->getEmail()]), 'text/html');
        $this->mailer->send($message);
    }
}
