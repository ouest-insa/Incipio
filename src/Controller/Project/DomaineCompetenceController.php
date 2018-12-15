<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Controller;

use Mgate\SuiviBundle\Entity\DomaineCompetence;
use Mgate\SuiviBundle\Form\Type\DomaineCompetenceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DomaineCompetenceController extends Controller
{
    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="MgateSuivi_domaine_index", path="/suivi/DomainesDeCompetence", methods={"GET","HEAD","POST"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('MgateSuiviBundle:DomaineCompetence')->findAll();

        $domaine = new DomaineCompetence();

        $form = $this->createForm(DomaineCompetenceType::class, $domaine);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($domaine);
                $em->flush();

                return $this->redirectToRoute('MgateSuivi_domaine_index');
            }
        }

        return $this->render('MgateSuiviBundle:DomaineCompetence:index.html.twig', [
            'domaines' => $entities,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="MgateSuivi_domaine_delete", path="/suivi/DomainesDeCompetence/Supprimer/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$domaine = $em->getRepository('Mgate\SuiviBundle\Entity\DomaineCompetence')->find($id)) {
            throw $this->createNotFoundException('Ce domaine de competence n\'existe pas !');
        }

        $em->remove($domaine);
        $em->flush();

        return $this->redirectToRoute('MgateSuivi_domaine_index');
    }
}
