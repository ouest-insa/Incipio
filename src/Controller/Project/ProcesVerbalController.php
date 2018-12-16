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


use App\Entity\Project\Etude;
use App\Entity\Project\ProcesVerbal;
use App\Form\Project\ProcesVerbalSubType;
use App\Form\Project\ProcesVerbalType;
use App\Service\Project\DocTypeManager;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProcesVerbalController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_procesverbal_ajouter", path="/suivi/procesverbal/ajouter/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param Etude                  $etude
     * @param EtudePermissionChecker $permChecker
     * @param DocTypeManager         $docTypeManager
     *
     * @return RedirectResponse|Response
     */
    public function addAction(Request $request, Etude $etude, EtudePermissionChecker $permChecker,
                              DocTypeManager $docTypeManager)
    {
        $em = $this->getDoctrine()->getManager();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $proces = new ProcesVerbal();
        $etude->addPvi($proces);

        $form = $this->createForm(ProcesVerbalSubType::class, $proces,
            ['type' => 'pvi', 'prospect' => $etude->getProspect(), 'phases' => count($etude->getPhases()->getValues()),
            ]);
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $docTypeManager->checkSaveNewEmploye($proces);
                $em->persist($proces);
                $em->flush();
                $this->addFlash('success', 'PV ajouté');

                return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
            }
        }

        return $this->render('Project/ProcesVerbal/ajouter.html.twig', [
            'etude' => $etude,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_procesverbal_modifier", path="/suivi/procesverbal/modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param ProcesVerbal           $procesverbal
     * @param EtudePermissionChecker $permChecker
     * @param DocTypeManager         $docTypeManager
     *
     * @return RedirectResponse|Response
     */
    public function modifierAction(Request $request, ProcesVerbal $procesverbal, EtudePermissionChecker $permChecker,
                                   DocTypeManager $docTypeManager)
    {
        $em = $this->getDoctrine()->getManager();

        $etude = $procesverbal->getEtude();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(ProcesVerbalSubType::class, $procesverbal,
            ['type' => $procesverbal->getType(), 'prospect' => $procesverbal->getEtude()->getProspect(),
             'phases' => count($procesverbal->getEtude()->getPhases()->getValues()),
            ]);
        $deleteForm = $this->createDeleteForm($procesverbal->getId());
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $docTypeManager->checkSaveNewEmploye($procesverbal);
                $em->persist($procesverbal);
                $em->flush();
                $this->addFlash('success', 'PV modifié');

                return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
            }
        }

        return $this->render('Project/ProcesVerbal/modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'etude' => $procesverbal->getEtude(),
            'type' => $procesverbal->getType(),
            'procesverbal' => $procesverbal,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_procesverbal_rediger", path="/suivi/procesverbal/rediger/{id}/{type}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param Etude                  $etude
     * @param string                 $type PVR or PVRI
     * @param EtudePermissionChecker $permChecker
     *
     * @return RedirectResponse|Response
     */
    public function redigerAction(Request $request, Etude $etude, $type, EtudePermissionChecker $permChecker)
    {
        $em = $this->getDoctrine()->getManager();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        if (!$procesverbal = $etude->getDoc($type)) {
            $procesverbal = new ProcesVerbal();
            if ('PVR' == strtoupper($type)) {
                $etude->setPvr($procesverbal);
            }
            $procesverbal->setType($type);
        }

        $form = $this->createForm(ProcesVerbalType::class, $etude,
            ['type' => $type, 'prospect' => $etude->getProspect(), 'phases' => count($etude->getPhases()->getValues()),
            ]);
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($etude);
                $em->flush();
                $this->addFlash('success', 'PV rédigé');

                return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
            }
        }

        return $this->render('Project/ProcesVerbal/rediger.html.twig',
            ['form' => $form->createView(), 'etude' => $etude, 'type' => $type]
        );
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_procesverbal_supprimer", path="/suivi/procesverbal/supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param ProcesVerbal           $procesVerbal
     * @param EtudePermissionChecker $permChecker
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, ProcesVerbal $procesVerbal, EtudePermissionChecker $permChecker)
    {
        $form = $this->createDeleteForm($procesVerbal->getId());
        $form->handleRequest($request);
        $etude = $procesVerbal->getEtude();

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($permChecker->confidentielRefus($etude, $this->getUser())) {
                throw new AccessDeniedException('Cette étude est confidentielle');
            }

            $em->remove($procesVerbal);
            $em->flush();
            $this->addFlash('success', 'PV supprimé');
        } else {
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
    }

    private function createDeleteForm($id_pv)
    {
        return $this->createFormBuilder(['id' => $id_pv])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
