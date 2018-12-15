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

use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Entity\Phase;
use Mgate\SuiviBundle\Form\Type\PhasesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PhasesController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route(name="MgateSuivi_phases_modifier", path="/suivi/phases/modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(PhasesType::class, $etude, ['etude' => $etude]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if ($request->get('add')) {
                    $phaseNew = new Phase();
                    $phaseNew->setPosition(count($etude->getPhases()));
                    $phaseNew->setEtude($etude);
                    $etude->addPhase($phaseNew);
                }

                $em->persist($etude); // persist $etude / $form->getData()
                $em->flush();
                $this->addFlash('success', 'Phases enregistrées');

                return $this->redirectToRoute('MgateSuivi_phases_modifier', ['id' => $etude->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateSuiviBundle:Phase:phases.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }
}
