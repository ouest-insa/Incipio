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
use Mgate\SuiviBundle\Entity\Mission;
use Mgate\SuiviBundle\Entity\RepartitionJEH;
use Mgate\SuiviBundle\Form\Type\MissionsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MissionsController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateSuivi_missions_modifier", path="/suivi/missions/modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(),
            $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        /* Form handling */
        $form = $this->createForm(MissionsType::class, $etude, ['etude' => $etude]);
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                foreach ($form->get('missions') as $missionForm) {
                    $m = $missionForm->getData();
                    foreach ($missionForm->get('repartitionsJEH') as $repartitionForm) {
                        $r = $repartitionForm->getData();
                        /* @var RepartitionJEH $r */
                        $r->setMission($m);
                    }
                    /* @var Mission $m  */
                    $m->setEtude($etude);
                }

                $em->persist($etude);
                $em->flush();
                $this->addFlash('success', 'Mission enregistrée');

                return $this->redirectToRoute('MgateSuivi_missions_modifier', ['id' => $etude->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateSuiviBundle:Mission:missions.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }
}
