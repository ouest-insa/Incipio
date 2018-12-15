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

use Mgate\TresoBundle\Entity\BV;
use Mgate\TresoBundle\Form\Type\BVType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class BVController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_BV_index", path="/Tresorerie/BV", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $bvs = $em->getRepository('MgateTresoBundle:BV')->findAll();

        return $this->render('MgateTresoBundle:BV:index.html.twig', ['bvs' => $bvs]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     *
     * @param BV $bv
     *
     * @return Response
     * @Route(name="MgateTreso_BV_voir", path="/Tresorerie/BV/Voir/{id}", methods={"GET","HEAD"}, requirements={"id": "\d+"})
     */
    public function voirAction(BV $bv)
    {
        return $this->render('MgateTresoBundle:BV:voir.html.twig', ['bv' => $bv]);
    }

    /**
     * @Security("has_role('ROLE_TRESO', 'ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param $id
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateTreso_BV_ajouter", path="/Tresorerie/BV/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1"})
     * @Route(name="MgateTreso_BV_modifier", path="/Tresorerie/BV/Modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$bv = $em->getRepository('MgateTresoBundle:BV')->find($id)) {
            $bv = new BV();
            $bv->setTypeDeTravail('Réalisateur')
                ->setDateDeVersement(new \DateTime('now'))
                ->setDateDemission(new \DateTime('now'));
        }

        $form = $this->createForm(BVType::class, $bv);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $bv->setCotisationURSSAF();
                $charges = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->findAllByDate($bv->getDateDemission());
                foreach ($charges as $charge) {
                    $bv->addCotisationURSSAF($charge);
                }
                if (null === $charges) {
                    $this->addFlash('danger', 'Il n\'y a aucune cotisation Urssaf définie pour cette période. 
                    Pour ajouter des cotisations URSSAF : ' . $this->get('router')->generate('MgateTreso_CotisationURSSAF_index') . '.');

                    return $this->redirectToRoute('MgateTreso_BV_index');
                }

                $baseURSSAF = $em->getRepository('MgateTresoBundle:BaseURSSAF')->findByDate($bv->getDateDemission());
                if (null === $baseURSSAF) {
                    $this->addFlash('danger', 'Il n\'y a aucune base Urssaf définie pour cette période. 
                    Pour ajouter une base URSSAF : ' . $this->get('router')->generate('MgateTreso_BaseURSSAF_index') . '.');

                    return $this->redirectToRoute('MgateTreso_BV_index');
                }
                $bv->setBaseURSSAF($baseURSSAF);

                $em->persist($bv);
                $em->flush();
                $this->addFlash('success', 'BV enregistré');

                return $this->redirectToRoute('MgateTreso_BV_index', []);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateTresoBundle:BV:modifier.html.twig', [
            'form' => $form->createView(),
            'bv' => $bv,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param BV $bv
     *
     * @return RedirectResponse
     * @Route(name="MgateTreso_BV_supprimer", path="/Tresorerie/BV/Supprimer/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     * @Route(name="MgateTreso_CotisationURSSAF_supprimer", path="/Tresorerie/CotisationURSSAF/Supprimer/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function supprimerAction(BV $bv)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($bv);
        $em->flush();
        $this->addFlash('success', 'BV supprimé');

        return $this->redirectToRoute('MgateTreso_BV_index', []);
    }
}
