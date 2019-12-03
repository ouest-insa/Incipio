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

use App\Entity\Project\AvMission;
use App\Entity\Project\Etude;
use App\Form\Project\AvMissionType;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AvMissionController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_avmission_ajouter", path="/suivi/avmission/ajouter/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @param Request                $request
     * @param Etude                  $etude
     * @param EtudePermissionChecker $permChecker
     *
     * @return RedirectResponse|Response
     */
    public function add(Request $request, Etude $etude, EtudePermissionChecker $permChecker)
    {
        $em = $this->getDoctrine()->getManager();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $avmission = new AvMission();
        $avmission->setEtude($etude);
        $form = $this->createForm(AvMissionType::class, $avmission);
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($avmission);
                $em->flush();
                $this->addFlash('success', 'Avenant de mission ajouté');

                return $this->redirectToRoute('project_etude_voir', ['nom' => $etude->getNom(), '_fragment' => 'tab3']);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('Project/AvMission/ajouter.html.twig', [
            'etude' => $etude,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_avmission_modifier", path="/suivi/avmission/modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param AvMission              $avmission
     * @param EtudePermissionChecker $permChecker
     *
     * @return RedirectResponse|Response
     */
    public function modifier(Request $request, AvMission $avmission, EtudePermissionChecker $permChecker)
    {
        $em = $this->getDoctrine()->getManager();

        $etude = $avmission->getEtude();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(AvMissionType::class, $avmission);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                return $this->redirectToRoute('project_etude_voir', ['nom' => $etude->getNom(), '_fragment' => 'tab3']);
            }
        }
        $deleteForm = $this->createDeleteForm($avmission);

        return $this->render('Project/AvMission/modifier.html.twig', [
            'etude' => $etude,
            'delete_form' => $deleteForm->createView(),
            'form' => $form->createView(),
            'avmission' => $avmission,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_avmission_delete", path="/suivi/avmission/supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param AvMission              $av
     * @param Request                $request
     * @param EtudePermissionChecker $permChecker
     *
     * @return RedirectResponse
     */
    public function delete(AvMission $av, Request $request, EtudePermissionChecker $permChecker)
    {
        $form = $this->createDeleteForm($av);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($permChecker->confidentielRefus($av->getEtude(), $this->getUser())) {
                throw new AccessDeniedException('Cette étude est confidentielle');
            }

            $em->remove($av);
            $em->flush();
            $this->addFlash('success', 'Avenant au RM supprimé');
        }

        return $this->redirectToRoute('project_etude_voir', ['nom' => $av->getEtude()->getNom(), '_fragment' => 'tab3']);
    }

    private function createDeleteForm(AvMission $contact)
    {
        return $this->createFormBuilder(['id' => $contact->getId()])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
