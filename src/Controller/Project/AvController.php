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

use App\Entity\Project\Av;
use App\Entity\Project\Etude;
use App\Form\Project\AvType;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AvController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_av_ajouter", path="/suivi/av/ajouter/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @param Request      $request
     * @param Etude        $etude
     * @param EtudePermissionChecker $permChecker
     *
     * @return RedirectResponse|Response
     */
    public function addAction(Request $request, Etude $etude, EtudePermissionChecker $permChecker)
    {
        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
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

        return $this->render('Project/Av/ajouter.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
            'av' => $av,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_av_modifier", path="/suivi/av/modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @param Request      $request
     * @param Av           $av
     * @param EtudePermissionChecker $permChecker
     *
     * @return RedirectResponse|Response
     */
    public function modifierAction(Request $request, Av $av, EtudePermissionChecker $permChecker)
    {
        $em = $this->getDoctrine()->getManager();

        $etude = $av->getEtude();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
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

        return $this->render('Project/Av/modifier.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
            'av' => $av,
        ]);
    }
}
