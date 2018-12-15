<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\TresoBundle\Controller;

use Mgate\TresoBundle\Entity\CotisationURSSAF;
use Mgate\TresoBundle\Form\Type\CotisationURSSAFType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class CotisationURSSAFController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_CotisationURSSAF_index", path="/Tresorerie/CotisationsURSSAF", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $cotisations = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->findAll();

        return $this->render('MgateTresoBundle:CotisationURSSAF:index.html.twig', ['cotisations' => $cotisations]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_CotisationURSSAF_ajouter", path="/Tresorerie/CotisationURSSAF/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1"})
     * @Route(name="MgateTreso_CotisationURSSAF_modifier", path="/Tresorerie/CotisationURSSAF/Modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$cotisation = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->find($id)) {
            $cotisation = new CotisationURSSAF();
        }

        $form = $this->createForm(CotisationURSSAFType::class, $cotisation);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($cotisation);
                $em->flush();

                return $this->redirectToRoute('MgateTreso_CotisationURSSAF_index', []);
            }
        }

        return $this->render('MgateTresoBundle:CotisationURSSAF:modifier.html.twig', [
                    'form' => $form->createView(),
                    'cotisation' => $cotisation,
                ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$cotisation = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->find($id)) {
            throw $this->createNotFoundException('La Cotisation URSSAF n\'existe pas !');
        }

        $em->remove($cotisation);
        $em->flush();

        return $this->redirectToRoute('MgateTreso_CotisationURSSAF_index', []);
    }
}
