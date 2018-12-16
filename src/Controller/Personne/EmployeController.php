<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Personne;

use App\Entity\Personne\Employe;
use App\Entity\Personne\Prospect;
use App\Form\Personne\EmployeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmployeController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="personne_employe_ajouter", path="/employe/add/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request  $request
     * @param Prospect $prospect
     *
     * @return RedirectResponse|Response
     */
    public function ajouter(Request $request, Prospect $prospect)
    {
        $em = $this->getDoctrine()->getManager();

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

                return $this->redirectToRoute('personne_prospect_voir', ['id' => $employe->getProspect()->getId()]);
            }
        }

        return $this->render('Personne/Employe/ajouter.html.twig', [
            'form' => $form->createView(),
            'prospect' => $prospect,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="personne_employe_modifier", path="/employe/modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     * @param Employe $employe
     *
     * @return RedirectResponse|Response
     */
    public function modifier(Request $request, Employe $employe)
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

                return $this->redirectToRoute('personne_prospect_voir', ['id' => $employe->getProspect()->getId()]);
            }
        }

        //to avoid asynchronous request at display time
        $prospect = $em->getRepository(Prospect::class)->findOneById($employe->getProspect()->getId());

        return $this->render('Personne/Employe/modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'employe' => $employe,
            'prospect' => $prospect,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="personne_employe_supprimer", path="/employe/supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Employe $employe the employee to delete
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function delete(Employe $employe, Request $request)
    {
        $form = $this->createDeleteForm($employe->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //remove employes
            $em->remove($employe);
            $em->flush();
            $this->addFlash('success', 'Employé supprimé');

            return $this->redirectToRoute('personne_prospect_voir', ['id' => $employe->getProspect()->getId()]);
        }

        return $this->redirectToRoute('personne_prospect_homepage');
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm()
            ;
    }
}
