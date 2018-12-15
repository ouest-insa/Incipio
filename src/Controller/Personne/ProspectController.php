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

use Mgate\PersonneBundle\Entity\Prospect;
use Mgate\PersonneBundle\Form\Type\ProspectType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProspectController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param         $format
     *
     * @return RedirectResponse|Response
     */
    public function ajouterAction(Request $request, $format)
    {
        $em = $this->getDoctrine()->getManager();
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

        return $this->render('MgatePersonneBundle:Prospect:ajouter.html.twig', [
            'form' => $form->createView(),
            'format' => $format,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgatePersonneBundle:Prospect')->getAllProspect();

        return $this->render('MgatePersonneBundle:Prospect:index.html.twig', [
            'prospects' => $entities,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Prospect $prospect
     *
     * @return Response
     */
    public function voirAction(Prospect $prospect)
    {
        $em = $this->getDoctrine()->getManager();

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

        return $this->render('MgatePersonneBundle:Prospect:voir.html.twig', [
            'prospect' => $prospect,
            'mailing' => $mailing,
            'etudes' => $etudes,
            ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request  $request
     * @param Prospect $prospect
     *
     * @return RedirectResponse|Response
     */
    public function modifierAction(Request $request, Prospect $prospect)
    {
        $em = $this->getDoctrine()->getManager();

        // On passe l'$article récupéré au formulaire
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

        return $this->render('MgatePersonneBundle:Prospect:modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'prospect' => $prospect,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Prospect $prospect the prospect to delete
     * @param Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Prospect $prospect, Request $request)
    {
        $form = $this->createDeleteForm($prospect->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

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
     *
     * @param Request $request
     *
     * @return Response
     */
    public function ajaxProspectAction(Request $request)
    {
        $value = $request->get('term');

        $em = $this->getDoctrine()->getManager();
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
        foreach ($employes as $employe) {
            array_push($json, ['label' => $employe->__toString(), 'value' => $employe->getId()]);
        }
        $response = new JsonResponse();
        $response->setData($json);

        return $response;
    }
}
