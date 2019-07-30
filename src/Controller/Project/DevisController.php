<?php

namespace App\Controller\Project;

use App\Entity\Project\Devis;
use App\Entity\Project\Etude;
use App\Form\Project\DevisType;
use App\Service\Project\DocTypeManager;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DevisController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_devis_rediger", path="/suivi/devis/rediger/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param Etude                  $etude          etude which CC should belong to
     * @param EtudePermissionChecker $permChecker
     * @param DocTypeManager         $docTypeManager
     *
     * @return RedirectResponse|Response
     */
    public function rediger(Request $request, Etude $etude, EtudePermissionChecker $permChecker, DocTypeManager $docTypeManager)
    {
        $em = $this->getDoctrine()->getManager();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        if (!$devis = $etude->getDevis()) {
            $devis = new Devis();
            $etude->setDevis($devis);
        }

        $form = $this->createForm(DevisType::class, $etude/*, ['prospect' => $etude->getProspect()]*/);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $docTypeManager->checkSaveNewEmploye($etude->getDevis());
                $em->flush();

                return $this->redirectToRoute('project_etude_voir', ['nom' => $etude->getNom(), '_fragment' => 'tab3']);
            }
        }

        /*return $this->render('Project/Devis/rediger.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);*/
        return $this->render('Project/Devis/rediger.html.twig', [
            'controller_name' => 'DevisController',
        ]);
    }
}
