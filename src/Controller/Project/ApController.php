<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Project;

use App\Entity\Project\Ap;
use App\Entity\Project\Etude;
use App\Form\Project\ApType;
use App\Form\Project\DocTypeSuiviType;
use App\Service\Project\DocTypeManager;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ApController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_ap_rediger", path="/suivi/ap/rediger/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @param Request                $request
     * @param Etude                  $etude          etude which Ap should be edited
     * @param EtudePermissionChecker $permChecker
     * @param DocTypeManager         $docTypeManager
     *
     * @return RedirectResponse|Response
     */
    public function redigerAction(Request $request, Etude $etude, EtudePermissionChecker $permChecker, DocTypeManager $docTypeManager)
    {
        $em = $this->getDoctrine()->getManager();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
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
                $docTypeManager->checkSaveNewEmploye($etude->getAp());
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

        return $this->render('Project/Ap/rediger.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_ap_suivi", path="/suivi/ap/suivi/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @param Request                $request
     * @param Etude                  $etude
     * @param EtudePermissionChecker $permChecker
     *
     * @return RedirectResponse|Response
     */
    public function suiviAction(Request $request, Etude $etude, EtudePermissionChecker $permChecker)
    {
        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
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

        return $this->render('Project/Ap/rediger.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }
}
