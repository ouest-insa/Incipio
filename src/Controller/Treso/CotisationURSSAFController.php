<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Treso;

use App\Entity\Treso\CotisationURSSAF;
use App\Form\Treso\CotisationURSSAFType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CotisationURSSAFController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_CotisationURSSAF_index", path="/Tresorerie/CotisationsURSSAF", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $cotisations = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->findAll();

        return $this->render('Treso/CotisationURSSAF/index.html.twig', ['cotisations' => $cotisations]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_CotisationURSSAF_ajouter", path="/Tresorerie/CotisationURSSAF/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1"})
     * @Route(name="MgateTreso_CotisationURSSAF_modifier", path="/Tresorerie/CotisationURSSAF/Modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     * @param         $id
     *
     * @return RedirectResponse|Response
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

        return $this->render('Treso/CotisationURSSAF/modifier.html.twig', [
                    'form' => $form->createView(),
                    'cotisation' => $cotisation,
                ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="MgateTreso_CotisationURSSAF_supprimer", path="/Tresorerie/CotisationURSSAF/Supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param CotisationURSSAF $cotisation
     *
     * @return RedirectResponse
     */
    public function supprimerAction(CotisationURSSAF $cotisation)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($cotisation);
        $em->flush();

        return $this->redirectToRoute('MgateTreso_CotisationURSSAF_index', []);
    }
}
