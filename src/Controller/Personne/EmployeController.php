<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle\Controller;

use Mgate\PersonneBundle\Entity\Employe;
use Mgate\PersonneBundle\Form\Type\EmployeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

class EmployeController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajouterAction(Request $request, $prospect_id, $format)
    {
        $em = $this->getDoctrine()->getManager();

        // On vérifie que le prospect existe bien
        if (!$prospect = $em->getRepository('Mgate\PersonneBundle\Entity\Prospect')->find($prospect_id)) {
            throw $this->createNotFoundException('Ce prospect n\'existe pas');
        }

        $employe = new Employe();
        $employe->setProspect($prospect);

        $form = $this->createForm(EmployeType::class, $employe);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($employe->getPersonne());
                $em->persist($employe);
                $employe->getPersonne()->setEmploye($employe);
                $em->flush();
                $this->addFlash('success', 'Employé ajouté');

                return $this->redirectToRoute('MgatePersonne_prospect_voir', ['id' => $employe->getProspect()->getId()]);
            }
        }

        return $this->render('MgatePersonneBundle:Employe:ajouter.html.twig', [
            'form' => $form->createView(),
            'prospect' => $prospect,
            'format' => $format,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction(Request $request, Employe $employe)
    {
        $em = $this->getDoctrine()->getManager();

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(EmployeType::class, $employe);
        $deleteForm = $this->createDeleteForm($employe->getId());
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($employe);
                $em->flush();
                $this->addFlash('success', 'Employé modifié');

                return $this->redirectToRoute('MgatePersonne_prospect_voir', ['id' => $employe->getProspect()->getId()]);
            }
        }

        //to avoid asynchronous request at display time
        $prospect = $em->getRepository('MgatePersonneBundle:Prospect')->findOneById($employe->getProspect()->getId());

        return $this->render('MgatePersonneBundle:Employe:modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'employe' => $employe,
            'prospect' => $prospect,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Employe $employe the employee to delete
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Employe $employe, Request $request)
    {
        $form = $this->createDeleteForm($employe->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //remove employes
            $em->remove($employe);
            $em->flush();
            $this->addFlash('success', 'Employé supprimé');

            return $this->redirectToRoute('MgatePersonne_prospect_voir', ['id' => $employe->getProspect()->getId()]);
        }

        return $this->redirectToRoute('MgatePersonne_prospect_homepage');
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm()
            ;
    }
}
