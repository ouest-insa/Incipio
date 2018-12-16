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


use App\Entity\Personne\Filiere;
use App\Entity\Personne\Membre;
use App\Form\Personne\FiliereType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FiliereController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="MgatePersonne_filiere_ajouter", path="/filiere/add", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
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

        return $this->render('Personne/Filiere/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="MgatePersonne_filiere_modifier", path="/filiere/modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     * @param Filiere $filiere
     *
     * @return RedirectResponse|Response
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

        return $this->render('Personne/Filiere/modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="MgatePersonne_filiere_supprimer", path="/filiere/supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     * @param Filiere $filiere
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Filiere $filiere)
    {
        $form = $this->createDeleteForm($filiere->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (0 == count($em->getRepository(Membre::class)->findByFiliere($filiere))) { //no members uses that filiere
                $em->remove($filiere);
                $em->flush();
                $this->addFlash('success', 'Filière supprimée avec succès');

                return $this->redirectToRoute('MgatePersonne_poste_homepage');
            }
            $this->addFlash('danger', 'Impossible de supprimer une filiere ayant des membres.');
        } else {
            $this->addFlash('danger', 'Formulaire invalide');
        }

        return $this->redirectToRoute('MgatePersonne_poste_homepage');
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
