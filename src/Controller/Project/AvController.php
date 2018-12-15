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

use Mgate\SuiviBundle\Entity\Av;
use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Form\Type\AvType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AvController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateSuivi_av_ajouter", path="/suivi/av/ajouter/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function addAction(Request $request, Etude $etude)
    {
        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }
        $em = $this->getDoctrine()->getManager();

        $av = new Av();
        $av->setEtude($etude);
        $etude->addAv($av);

        $form = $this->createForm(AvType::class, $av, ['prospect' => $etude->getProspect()]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($av);
                $em->flush();
                $this->addFlash('success', 'Avenant enregistré');

                return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateSuiviBundle:Av:ajouter.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
            'av' => $av,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Av      $av
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateSuivi_av_modifier", path="/suivi/av/modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, Av $av)
    {
        $em = $this->getDoctrine()->getManager();

        $etude = $av->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(AvType::class, $av, ['prospect' => $av->getEtude()->getProspect()]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($av);
                $em->flush();
                $this->addFlash('success', 'Avenant enregistré');

                return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateSuiviBundle:Av:modifier.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
            'av' => $av,
        ]);
    }
}
