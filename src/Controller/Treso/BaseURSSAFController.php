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

use Mgate\TresoBundle\Entity\BaseURSSAF;
use Mgate\TresoBundle\Form\Type\BaseURSSAFType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class BaseURSSAFController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_BaseURSSAF_index", path="/Tresorerie/BasesURSSAF", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $bases = $em->getRepository('MgateTresoBundle:BaseURSSAF')->findAll();

        return $this->render('MgateTresoBundle:BaseURSSAF:index.html.twig', ['bases' => $bases]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_BaseURSSAF_ajouter", path="/Tresorerie/BaseURSSAF/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1"})
     * @Route(name="MgateTreso_BaseURSSAF_modifier", path="/Tresorerie/BaseURSSAF/Modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$base = $em->getRepository('MgateTresoBundle:BaseURSSAF')->find($id)) {
            $base = new BaseURSSAF();
        }

        $form = $this->createForm(BaseURSSAFType::class, $base);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($base);
                $em->flush();

                return $this->redirectToRoute('MgateTreso_BaseURSSAF_index', []);
            }
        }

        return $this->render('MgateTresoBundle:BaseURSSAF:modifier.html.twig', [
                    'form' => $form->createView(),
                    'base' => $base,
                ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="MgateTreso_BaseURSSAF_supprimer", path="/Tresorerie/BaseURSSAF/Supprimer/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$base = $em->getRepository('MgateTresoBundle:BaseURSSAF')->find($id)) {
            throw $this->createNotFoundException('La base URSSAF n\'existe pas !');
        }

        $em->remove($base);
        $em->flush();

        return $this->redirectToRoute('MgateTreso_BaseURSSAF_index', []);
    }
}
