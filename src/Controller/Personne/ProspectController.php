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
use App\Form\Personne\ProspectType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProspectController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgatePersonne_prospect_ajouter", path="/prospect/add/", methods={"GET","HEAD","POST"})
     *
     * @param Request       $request
     * @param ObjectManager $em
     *
     * @return RedirectResponse|Response
     */
    public function ajouterAction(Request $request, ObjectManager $em)
    {
        $prospect = new Prospect();

        $form = $this->createForm(ProspectType::class, $prospect);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($prospect);
                $em->flush();
                $this->addFlash('success', 'Prospect enregistré');

                return $this->redirectToRoute('MgatePersonne_prospect_voir', ['id' => $prospect->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('Personne/Prospect/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgatePersonne_prospect_homepage", path="/prospect/", methods={"GET","HEAD"})
     *
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgatePersonneBundle:Prospect')->getAllProspect();

        return $this->render('Personne/Prospect/index.html.twig', [
            'prospects' => $entities,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgatePersonne_prospect_voir", path="/prospect/voir/{id}", methods={"GET","HEAD"})
     *
     * @param Prospect      $prospect
     * @param ObjectManager $em
     *
     * @return Response
     */
    public function voirAction(Prospect $prospect, ObjectManager $em)
    {
        //récupération des employés
        $mailing = '';
        $employes = [];
        foreach ($prospect->getEmployes() as $employe) {
            if ($employe->getPersonne()->getEmailEstValide() && $employe->getPersonne()->getEstAbonneNewsletter()) {
                $nom = $employe->getPersonne()->getNom();
                $mail = $employe->getPersonne()->getEmail();
                $employes[$nom] = $mail;
            }
        }
        ksort($employes);
        foreach ($employes as $nom => $mail) {
            $mailing .= "$nom <$mail>; ";
        }

        //récupération des études faites avec ce prospect
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findByProspect($prospect);

        return $this->render('Personne/Prospect/voir.html.twig', [
            'prospect' => $prospect,
            'mailing' => $mailing,
            'etudes' => $etudes,
            ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgatePersonne_prospect_modifier", path="/prospect/modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request       $request
     * @param Prospect      $prospect
     * @param ObjectManager $em
     *
     * @return RedirectResponse|Response
     */
    public function modifierAction(Request $request, Prospect $prospect, ObjectManager $em)
    {
        $form = $this->createForm(ProspectType::class, $prospect);
        $deleteForm = $this->createDeleteForm($prospect->getId());
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($prospect);
                $em->flush();
                $this->addFlash('success', 'Prospect enregistré');

                return $this->redirectToRoute('MgatePersonne_prospect_voir', ['id' => $prospect->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('Personne/Prospect/modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'prospect' => $prospect,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgatePersonne_prospect_supprimer", path="/prospect/supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Prospect      $prospect the prospect to delete
     * @param Request       $request
     * @param ObjectManager $em
     *
     * @return RedirectResponse
     */
    public function deleteAction(Prospect $prospect, Request $request, ObjectManager $em)
    {
        $form = $this->createDeleteForm($prospect->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $related_projects = $em->getRepository('MgateSuiviBundle:Etude')->findByProspect($prospect);

            if (count($related_projects) > 0) {
                //can't delete a prospect with related projects
                $this->addFlash('warning', 'Impossible de supprimer un prospect ayant une étude liée.');

                return $this->redirectToRoute('MgatePersonne_prospect_voir', ['id' => $prospect->getId()]);
            } else {
                //remove employes
                foreach ($prospect->getEmployes() as $employe) {
                    $em->remove($employe);
                }
                $em->remove($prospect);
                $em->flush();
                $this->addFlash('success', 'Prospect supprimé');
            }
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

    /**
     * Point d'entré ajax retournant un json des prospect dont le nom contient une partie de $_GET['term'].
     *
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgatePersonne_ajax_prospect", path="/ajax/ajax_prospect/", methods={"GET","HEAD"})
     *
     * @param Request       $request
     * @param ObjectManager $em
     *
     * @return Response
     */
    public function ajaxProspectAction(Request $request, ObjectManager $em)
    {
        $value = $request->get('term');
        $members = $em->getRepository('MgatePersonneBundle:Prospect')->ajaxSearch($value);

        $json = [];
        foreach ($members as $member) {
            $json[] = [
                'label' => $member->getNom(),
                'value' => $member->getId(),
            ];
        }

        $response = new Response();
        $response->setContent(json_encode($json));

        return $response;
    }

    /**
     * Point d'entrée ajax retournant un Json avec la liste des employés d'un prospect donné.
     *
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgatePersonne_ajax_employes_de_prospect", path="/ajax/employes_de_prospect/{id}", methods={"GET","HEAD"})
     *
     * @param Prospect $prospect
     *
     * @return JsonResponse
     */
    public function ajaxEmployesAction(Prospect $prospect)
    {
        $em = $this->getDoctrine()->getManager();
        $employes = $em->getRepository('MgatePersonneBundle:Employe')->findByProspect($prospect);
        $json = [];
        /** @var Employe $employe */
        foreach ($employes as $employe) {
            array_push($json, ['label' => $employe->__toString(), 'value' => $employe->getId()]);
        }
        $response = new JsonResponse();
        $response->setData($json);

        return $response;
    }
}
