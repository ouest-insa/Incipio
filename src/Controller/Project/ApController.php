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

use Mgate\SuiviBundle\Entity\Ap;
use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Form\Type\ApType;
use Mgate\SuiviBundle\Form\Type\DocTypeSuiviType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ApController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude   etude which Ap should be edited
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route(name="MgateSuivi_ap_rediger", path="/suivi/ap/rediger/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function redigerAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        if (!$ap = $etude->getAp()) {
            $ap = new Ap();
            $etude->setAp($ap);
        }

        $form = $this->createForm(ApType::class, $etude, ['prospect' => $etude->getProspect()]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->get('Mgate.doctype_manager')->checkSaveNewEmploye($etude->getAp());
                $em->flush();

                $this->addFlash('success', 'Avant-Projet modifié');
                if ($request->get('phases')) {
                    return $this->redirectToRoute('MgateSuivi_phases_modifier', ['id' => $etude->getId()]);
                } else {
                    return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
                }
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateSuiviBundle:Ap:rediger.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route(name="MgateSuivi_ap_suivi", path="/suivi/ap/suivi/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function suiviAction(Request $request, Etude $etude)
    {
        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $ap = $etude->getAp();
        $form = $this->createForm(DocTypeSuiviType::class, $ap); //transmettre etude pour ajouter champ de etude

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
            }
        }

        return $this->render('MgateSuiviBundle:Ap:rediger.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }
}
