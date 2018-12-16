<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Formation;

use App\Entity\Formation\Formation;
use App\Form\Formation\FormationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="Mgate_formations_index_admin", path="/formations/admin", methods={"GET","HEAD"})
     *
     * Display a list of all training given order by date desc
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $formations = $em->getRepository('MgateFormationBundle:Formation')
            ->getAllFormations([], ['dateDebut' => 'DESC']);

        return $this->render('Formation/Gestion/index.html.twig', [
            'formations' => $formations,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="Mgate_formations_lister", path="/formations", methods={"GET","HEAD"})
     *
     * Display a list of all training group by term.
     */
    public function listerAction()
    {
        $em = $this->getDoctrine()->getManager();
        $formationsParMandat = $em->getRepository('MgateFormationBundle:Formation')->findAllByMandat();

        return $this->render('Formation/Formations/lister.html.twig', [
            'formationsParMandat' => $formationsParMandat,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="Mgate_formation_voir", path="/formations/{id}", methods={"GET","HEAD"}, requirements={"id": "\d+"})
     *
     * @param Formation $formation The training to display
     *
     * @return Response
     */
    public function voirAction(Formation $formation)
    {
        return $this->render('Formation/Formations/voir.html.twig', [
            'formation' => $formation,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="Mgate_formation_ajouter", path="/formations/admin/ajouter", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     *
     * @return Response
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

        return $this->render('Formation/Gestion/ajouter.html.twig', ['form' => $form->createView(),
                                                                                'formation' => $formation,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="Mgate_formation_modifier", path="/formations/admin/modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @param Request   $request
     * @param Formation $formation The training to modify
     *
     * @return Response
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

        return $this->render('Formation/Gestion/modifier.html.twig', [
            'delete_form' => $deleteForm->createView(),
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="Mgate_formation_participation", path="/formations/admin/participation/{mandat}", methods={"GET","HEAD"}, defaults={"mandat": ""})
     *
     * @param $mandat string The mandat during which trainings were given
     *
     * @return Response Manage participant present to a training
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

        return $this->render('Formation/Gestion/participation.html.twig', [
            'form' => $form->createView(),
            'formations' => $formations,
            'presents' => $presents,
            'mandat' => $mandat,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="Mgate_formation_supprimer", path="/formations/admin/supprimer/{id}", methods={"HEAD","POST"})
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
            ->getForm();
    }
}
