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

use Mgate\TresoBundle\Entity\Compte;
use Mgate\TresoBundle\Form\Type\CompteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class CompteController extends Controller
{
    /**
     * @Route(name="MgateTreso_Compte_index", path="/Tresorerie/Comptes", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $comptes = $em->getRepository('MgateTresoBundle:Compte')->findAll();

        return $this->render('MgateTresoBundle:Compte:index.html.twig', ['comptes' => $comptes]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_Compte_ajouter", path="/Tresorerie/Compte/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1", "etude_id": "-1"})
     * @Route(name="MgateTreso_Compte_modifier", path="/Tresorerie/Compte/Modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$compte = $em->getRepository('MgateTresoBundle:Compte')->find($id)) {
            $compte = new Compte();
        }

        $form = $this->createForm(CompteType::class, $compte);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($compte);
                $em->flush();

                return $this->redirectToRoute('MgateTreso_Compte_index', []);
            }
        }

        return $this->render('MgateTresoBundle:Compte:modifier.html.twig', [
                    'form' => $form->createView(),
                    'compte' => $compte,
                ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="MgateTreso_Compte_supprimer", path="/Tresorerie/Compte/Supprimer/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$compte = $em->getRepository('MgateTresoBundle:Compte')->find($id)) {
            throw $this->createNotFoundException('Le Compte n\'existe pas !');
        }

        $em->remove($compte);
        $em->flush();

        return $this->redirectToRoute('MgateTreso_Compte_index', []);
    }
}
