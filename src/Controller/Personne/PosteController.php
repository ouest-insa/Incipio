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

use Mgate\PersonneBundle\Entity\Poste;
use Mgate\PersonneBundle\Form\Type\PosteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

class PosteController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $poste = new Poste();

        $form = $this->createForm(PosteType::class, $poste);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($poste);
                $em->flush();
                $this->addFlash('success', 'Poste ajouté');

                return $this->redirectToRoute('MgatePersonne_poste_homepage');
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgatePersonneBundle:Poste:ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $postes = $em->getRepository('MgatePersonneBundle:Poste')->findAll();
        $filieres = $em->getRepository('MgatePersonneBundle:Filiere')->findAll();

        return $this->render('MgatePersonneBundle:Poste:index.html.twig', [
            'postes' => $postes,
            'filieres' => $filieres,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$poste = $em->getRepository('Mgate\PersonneBundle\Entity\Poste')->find($id)) {
            throw $this->createNotFoundException('Le poste demandé n\'existe pas !');
        }

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(PosteType::class, $poste);
        $deleteForm = $this->createDeleteForm($id);
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($poste);
                $em->flush();
                $this->addFlash('success', 'Poste modifié');

                return $this->redirectToRoute('MgatePersonne_poste_homepage');
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgatePersonneBundle:Poste:modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function deleteAction(Request $request, Poste $poste)
    {
        $form = $this->createDeleteForm($poste->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (0 == $poste->getMandats()->count()) { //collection contains no mandats
                foreach ($poste->getMandats() as $membre) {
                    $membre->setPoste(null);
                }
                $em->remove($poste);
                $em->flush();
                $this->addFlash('success', 'Poste supprimé');

                return $this->redirectToRoute('MgatePersonne_poste_homepage');
            } else {
                $this->addFlash('danger', 'Impossible de supprimer un poste ayant des membres.');

                return $this->redirectToRoute('MgatePersonne_poste_modifier', ['id' => $poste->getId()]);
            }
        }
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
