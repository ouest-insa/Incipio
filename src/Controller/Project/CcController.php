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

use Mgate\SuiviBundle\Entity\Cc;
use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Form\Type\CcType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CcController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude   etude which CC should belong to
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route(name="MgateSuivi_cc_rediger", path="/suivi/cc/rediger/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function redigerAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        if (!$cc = $etude->getCc()) {
            $cc = new Cc();
            $etude->setCc($cc);
        }

        $form = $this->createForm(CcType::class, $etude, ['prospect' => $etude->getProspect()]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->get('Mgate.doctype_manager')->checkSaveNewEmploye($etude->getCc());
                $em->flush();

                return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
            }
        }

        return $this->render('MgateSuiviBundle:Cc:rediger.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }
}
