<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\FormationBundle\Controller;

use Mgate\FormationBundle\Entity\Formation;
use Mgate\FormationBundle\Form\Type\FormationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormationController extends Controller
{
    /**
     * @Security("has_role('ROLE_CA')")
     * Display a list of all training given order by date desc
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $formations = $em->getRepository('MgateFormationBundle:Formation')->getAllFormations([], ['dateDebut' => 'DESC']);

        return $this->render('MgateFormationBundle:Gestion:index.html.twig', [
            'formations' => $formations,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * Display a list of all training group by term.
     */
    public function listerAction()
    {
        $em = $this->getDoctrine()->getManager();
        $formationsParMandat = $em->getRepository('MgateFormationBundle:Formation')->findAllByMandat();

        return $this->render('MgateFormationBundle:Formations:lister.html.twig', [
            'formationsParMandat' => $formationsParMandat,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Formation $formation The training to display
     *
     * @return Response
     *                  Display a training
     */
    public function voirAction(Formation $formation)
    {
        return $this->render('MgateFormationBundle:Formations:voir.html.twig', [
            'formation' => $formation,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * @param Request $request
     *
     * @return Response
     *                  Manage creation of a training
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($formation);
                $em->flush();
                $this->addFlash('success', 'Formation enregistrée');

                return $this->redirectToRoute('Mgate_formation_voir', ['id' => $formation->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateFormationBundle:Gestion:ajouter.html.twig', ['form' => $form->createView(),
            'formation' => $formation,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * @param Request   $request
     * @param Formation $formation The training to modify
     *
     * @return Response Manage creation and update of a training
     */
    public function modifierAction(Request $request, Formation $formation)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(FormationType::class, $formation);
        $deleteForm = $this->createDeleteForm($formation->getId());

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($formation);
                $em->flush();
                $this->addFlash('success', 'Formation enregistrée');

                return $this->redirectToRoute('Mgate_formation_voir', ['id' => $formation->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateFormationBundle:Gestion:modifier.html.twig', [
            'delete_form' => $deleteForm->createView(),
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * @param $mandat string The mandat during which trainings were given
     *
     * @return Response
     *                  Manage participant present to a training
     */
    public function participationAction($mandat = null)
    {
        $em = $this->getDoctrine()->getManager();
        $formationsParMandat = $em->getRepository('MgateFormationBundle:Formation')->findAllByMandat();

        $choices = [];
        foreach ($formationsParMandat as $key => $value) {
            $choices[$key] = $key;
        }

        $defaultData = [];
        $form = $this->createFormBuilder($defaultData)
            ->add(
                'mandat',
                ChoiceType::class,
                [
                    'label' => 'Présents aux formations du mandat ',
                    'choices' => $choices,
                    'required' => true,
                ]
            )->getForm();

        if (null !== $mandat) {
            $formations = array_key_exists($mandat, $formationsParMandat) ? $formationsParMandat[$mandat] : [];
        } else {
            $formations = count($formationsParMandat) ? reset($formationsParMandat) : [];
        }

        $presents = [];

        foreach ($formations as $formation) {
            foreach ($formation->getMembresPresents() as $present) {
                $id = $present->getPrenomNom();
                if (array_key_exists($id, $presents)) {
                    $presents[$id][] = $formation->getId();
                } else {
                    $presents[$id] = [$formation->getId()];
                }
            }
        }

        return $this->render('MgateFormationBundle:Gestion:participation.html.twig', [
            'form' => $form->createView(),
            'formations' => $formations,
            'presents' => $presents,
            'mandat' => $mandat,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request   $request
     * @param Formation $formation The training to delete (paramconverter from id)
     *
     * @return RedirectResponse Delete a training
     */
    public function supprimerAction(Request $request, Formation $formation)
    {
        $form = $this->createDeleteForm($formation->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($formation);
            $em->flush();
            $this->addFlash('success', 'Formation supprimée');
        }

        return $this->redirectToRoute('Mgate_formations_lister', []);
    }

    /**
     * Function to create a form to remove a formation.
     *
     * @param $id
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm()
            ;
    }
}
