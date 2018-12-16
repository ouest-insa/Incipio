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

use App\Entity\Project\Cc;
use App\Entity\Project\Etude;
use App\Form\Project\CcType;
use App\Service\Project\DocTypeManager;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CcController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_cc_rediger", path="/suivi/cc/rediger/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param Etude                  $etude etude which CC should belong to
     * @param EtudePermissionChecker $permChecker
     * @param DocTypeManager         $docTypeManager
     *
     * @return RedirectResponse|Response
     */
    public function redigerAction(Request $request, Etude $etude, EtudePermissionChecker $permChecker, DocTypeManager $docTypeManager)
    {
        $em = $this->getDoctrine()->getManager();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        if (!$cc = $etude->getCc()) {
            $cc = new Cc();
            $etude->setCc($cc);
        }

        $form = $this->createForm(CcType::class, $etude, ['prospect' => $etude->getProspect()]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $docTypeManager->checkSaveNewEmploye($etude->getCc());
                $em->flush();

                return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
            }
        }

        return $this->render('Project/Cc/rediger.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }
}
