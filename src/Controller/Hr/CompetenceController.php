<?php

namespace App\Controller\Hr;

use App\Entity\Hr\Competence;
use App\Entity\Personne\Membre;
use App\Entity\Project\Etude;
use App\Form\Hr\CompetenceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompetenceController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="hr_competence_ajouter", path="/rh/competence/add", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function ajouter(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $competence = new Competence();

        $form = $this->createForm(CompetenceType::class, $competence);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($competence);
                $em->flush();

                return $this->redirectToRoute('hr_competence_voir', ['id' => $competence->getId()]);
            }
        }

        return $this->render('Hr/Competence/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="hr_competence_homepage", path="/rh/", methods={"GET","HEAD"})
     */
    public function index()
    {
        $entities = $this->getDoctrine()->getManager()->getRepository(Competence::class)->findBy([], ['nom' => 'asc']);

        return $this->render('Hr/Competence/index.html.twig', [
            'competences' => $entities,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="hr_competence_voir", path="/rh/competence/{id}", methods={"GET","HEAD"})
     *
     * @param Competence $skill
     *
     * @return Response
     */
    public function voir(Competence $skill)
    {
        $em = $this->getDoctrine()->getManager();

        $devs = $em->getRepository(Membre::class)->findByCompetence($skill);

        $etudes = $em->getRepository(Etude::class)->findByCompetence($skill);

        return $this->render('Hr/Competence/voir.html.twig', [
            'competence' => $skill,
            'devs' => $devs,
            'etudes' => $etudes,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="hr_competence_modifier", path="/rh/competence/modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request    $request
     * @param Competence $competence
     *
     * @return RedirectResponse|Response
     */
    public function modifier(Request $request, Competence $competence)
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

                return $this->redirectToRoute('hr_competence_voir', ['id' => $competence->getId()]);
            }
        }

        return $this->render('Hr/Competence/modifier.html.twig', [
            'competence' => $competence,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="hr_competence_visualiser", path="/rh/visualiser/competences", methods={"GET","HEAD"})
     *
     * Par souci de simplicité, on fait 2 requetes (une sur les competences, une sur les intervenants), alors que seule la requete sur les competences suffirait.
     */
    public function visualiser()
    {
        $em = $this->getDoctrine()->getManager();
        $competences = $em->getRepository(Competence::class)->getCompetencesTree();
        $membres = $em->getRepository(Membre::class)->getByCompetencesNonNul();

        $response = $this->render('Hr/Competence/visualiser.html.twig', [
            'total_liens' => 0,
            'competences' => $competences,
            'membres' => $membres,
        ]);
        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="hr_competence_supprimer", path="/rh/competence/supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request    $request
     * @param Competence $competence param converter on id
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Competence $competence)
    {
        $form = $this->createDeleteForm($competence->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($competence);
            $em->flush();
        }

        return $this->redirectToRoute('hr_competence_homepage');
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm()
            ;
    }
}
