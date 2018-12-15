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

use Mgate\PersonneBundle\Entity\Filiere;
use Mgate\PersonneBundle\Form\Type\FiliereType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

class FiliereController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $filiere = new Filiere();

        $form = $this->createForm(FiliereType::class, $filiere);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($filiere);
                $em->flush();
                $this->addFlash('success', 'Filière ajoutée');

                return $this->redirectToRoute('MgatePersonne_poste_homepage');
            }
        }

        return $this->render('MgatePersonneBundle:Filiere:ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param Filiere $filiere
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifierAction(Request $request, Filiere $filiere)
    {
        $em = $this->getDoctrine()->getManager();

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(FiliereType::class, $filiere);
        $deleteForm = $this->createDeleteForm($filiere->getId());

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($filiere);
                $em->flush();
                $this->addFlash('success', 'Filière modifiée');

                return $this->redirectToRoute('MgatePersonne_poste_homepage');
            }
        }

        return $this->render('MgatePersonneBundle:Filiere:modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param Filiere $filiere
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Filiere $filiere)
    {
        $form = $this->createDeleteForm($filiere->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (0 == count($em->getRepository('MgatePersonneBundle:Membre')->findByFiliere($filiere))) { //no members uses that filiere
                $em->remove($filiere);
                $em->flush();
                $this->addFlash('success', 'Filière supprimée avec succès');

                return $this->redirectToRoute('MgatePersonne_poste_homepage');
            } else {
                $this->addFlash('danger', 'Impossible de supprimer une filiere ayant des membres.');

                return $this->redirectToRoute('MgatePersonne_poste_homepage');
            }
        }
        $this->addFlash('danger', 'formulaire invalide');

        return $this->redirectToRoute('MgatePersonne_poste_homepage');
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm()
            ;
    }
}
