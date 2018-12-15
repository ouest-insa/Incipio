<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Controller;

use Mgate\SuiviBundle\Entity\ClientContact;
use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Form\Type\ClientContactHandler;
use Mgate\SuiviBundle\Form\Type\ClientContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ClientContactController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_clientcontact_index", path="/suivi/clientcontact/", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgateSuiviBundle:ClientContact')->findBy([], ['date' => 'ASC']);

        return $this->render('MgateSuiviBundle:ClientContact:index.html.twig', [
            'contactsClient' => $entities,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateSuivi_clientcontact_ajouter", path="/suivi/clientcontact/ajouter/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function addAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $clientcontact = new ClientContact();
        $clientcontact->setEtude($etude);
        $form = $this->createForm(ClientContactType::class, $clientcontact);
        $formHandler = new ClientContactHandler($form, $request, $em);

        if ($formHandler->process()) {
            return $this->redirectToRoute('MgateSuivi_clientcontact_voir', ['id' => $clientcontact->getId()]);
        }

        return $this->render('MgateSuiviBundle:ClientContact:ajouter.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }

    private function compareDate(ClientContact $a, ClientContact $b)
    {
        if ($a->getDate() == $b->getDate()) {
            return 0;
        } else {
            return ($a->getDate() < $b->getDate()) ? -1 : 1;
        }
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param ClientContact $clientContact
     *
     * @return Response
     * @Route(name="MgateSuivi_clientcontact_voir", path="/suivi/clientcontact/voir/{id}", methods={"GET","HEAD"}, requirements={"id": "\d+"})
     */
    public function voirAction(ClientContact $clientContact)
    {
        $etude = $clientContact->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $etude = $clientContact->getEtude();
        $contactsClient = $etude->getClientContacts()->toArray();
        usort($contactsClient, [$this, 'compareDate']);

        return $this->render('MgateSuiviBundle:ClientContact:voir.html.twig', [
            'contactsClient' => $contactsClient,
            'selectedContactClient' => $clientContact,
            'etude' => $etude,
            ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request       $request
     * @param ClientContact $clientContact
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateSuivi_clientcontact_modifier", path="/suivi/clientcontact/modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, ClientContact $clientContact)
    {
        $em = $this->getDoctrine()->getManager();

        $etude = $clientContact->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(ClientContactType::class, $clientContact);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();
                $this->addFlash('success', 'Contact client modifié');

                return $this->redirectToRoute('MgateSuivi_clientcontact_voir', ['id' => $clientContact->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }
        $deleteForm = $this->createDeleteForm($clientContact);

        return $this->render('MgateSuiviBundle:ClientContact:modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'clientcontact' => $clientContact,
            'etude' => $etude,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param ClientContact $contact
     * @param Request       $request
     *
     * @return RedirectResponse
     * @Route(name="MgateSuivi_clientcontact_delete", path="/suivi/clientcontact/supprimer/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function deleteAction(ClientContact $contact, Request $request)
    {
        $form = $this->createDeleteForm($contact);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($this->get('Mgate.etude_manager')->confidentielRefus($contact->getEtude(), $this->getUser())) {
                throw new AccessDeniedException('Cette étude est confidentielle');
            }

            $em->remove($contact);
            $em->flush();
            $this->addFlash('success', 'Contact client supprimé');
        }

        return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $contact->getEtude()->getNom()]);
    }

    private function createDeleteForm(ClientContact $contact)
    {
        return $this->createFormBuilder(['id' => $contact->getId()])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
