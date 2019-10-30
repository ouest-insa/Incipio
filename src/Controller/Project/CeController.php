<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Project;

use App\Entity\Project\Ce;
use App\Entity\Project\Etude;
use App\Form\Project\CeType;
use App\Service\Project\DocTypeManager;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CeController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_ce_rediger", path="/suivi/ce/rediger/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param Etude                  $etude          etude which CE should belong to
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

        if (!$ce = $etude->getCe()) {
            $ce = new Ce();
            $etude->setCe($ce);
        }

        $form = $this->createForm(CeType::class, $etude, ['prospect' => $etude->getProspect()]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $docTypeManager->checkSaveNewEmploye($etude->getCe());
                $em->flush();

                return $this->redirectToRoute('project_etude_voir', ['nom' => $etude->getNom()]);
            }
        }

        return $this->render('Project/Ce/rediger.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }
}
