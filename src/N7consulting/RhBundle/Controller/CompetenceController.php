<?php

namespace N7consulting\RhBundle\Controller;

use N7consulting\RhBundle\Entity\Competence;
use N7consulting\RhBundle\Form\Type\CompetenceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetenceController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $competence = new Competence();

        $form = $this->createForm(CompetenceType::class, $competence);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($competence);
                $em->flush();

                return $this->redirectToRoute('N7consultingRh_competence_voir', ['id' => $competence->getId()]);
            }
        }

        return $this->render('N7consultingRhBundle:Competence:ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction()
    {
        $entities = $this->getDoctrine()->getManager()->getRepository('N7consultingRhBundle:Competence')->findBy([], ['nom' => 'asc']);

        return $this->render('N7consultingRhBundle:Competence:index.html.twig', [
            'competences' => $entities,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Competence $skill
     *
     * @return Response
     */
    public function voirAction(Competence $skill)
    {
        $em = $this->getDoctrine()->getManager();

        $devs = $em->getRepository('MgatePersonneBundle:Membre')->findByCompetence($skill);

        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findByCompetence($skill);

        return $this->render('N7consultingRhBundle:Competence:voir.html.twig', [
            'competence' => $skill,
            'devs' => $devs,
            'etudes' => $etudes,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request    $request
     * @param Competence $competence
     *
     * @return RedirectResponse|Response
     */
    public function modifierAction(Request $request, Competence $competence)
    {
        $em = $this->getDoctrine()->getManager();

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(CompetenceType::class, $competence);
        $deleteForm = $this->createDeleteForm($competence->getId());
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($competence);
                $em->flush();

                return $this->redirectToRoute('N7consultingRh_competence_voir', ['id' => $competence->getId()]);
            }
        }

        return $this->render('N7consultingRhBundle:Competence:modifier.html.twig', [
            'competence' => $competence,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Par souci de simplicité, on fait 2 requetes (une sur les competences, une sur les intervenants), alors que seule la requete sur les competences suffirait.
     */
    public function visualiserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $competences = $em->getRepository('N7consulting\RhBundle\Entity\Competence')->getCompetencesTree();
        $membres = $em->getRepository('MgatePersonneBundle:Membre')->getByCompetencesNonNul();

        $response = $this->render('N7consultingRhBundle:Competence:visualiser.html.twig', [
            'total_liens' => 0,
            'competences' => $competences,
            'membres' => $membres,
        ]);
        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * @param Request    $request
     * @param Competence $competence param converter on id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Competence $competence)
    {
        $form = $this->createDeleteForm($competence->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($competence);
            $em->flush();
        }

        return $this->redirectToRoute('N7consultingRh_competence_homepage');
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm()
            ;
    }
}
