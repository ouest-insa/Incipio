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

use App\Entity\Treso\BaseURSSAF;
use App\Entity\Treso\BV;
use App\Entity\Treso\CotisationURSSAF;
use App\Form\Treso\BVType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class BVController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_BV_index", path="/Tresorerie/BV", methods={"GET","HEAD"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $bvs = $em->getRepository(BV::class)->findAll();

        return $this->render('Treso/BV/index.html.twig', ['bvs' => $bvs]);
    }

    /**
     * @Security("has_role('ROLE_TRESO', 'ROLE_SUIVEUR')")
     * @Route(name="treso_BV_ajouter", path="/Tresorerie/BV/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1"})
     * @Route(name="treso_BV_modifier", path="/Tresorerie/BV/Modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @param Request         $request
     * @param                 $id
     * @param RouterInterface $router
     *
     * @return RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function modifier(Request $request, $id, RouterInterface $router)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$bv = $em->getRepository(BV::class)->find($id)) {
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
                $charges = $em->getRepository(CotisationURSSAF::class)->findAllByDate($bv->getDateDemission());
                foreach ($charges as $charge) {
                    $bv->addCotisationURSSAF($charge);
                }
                if (null === $charges) {
                    $this->addFlash('danger', 'Il n\'y a aucune cotisation Urssaf définie pour cette période. 
                    Pour ajouter des cotisations URSSAF : ' . $router->generate('treso_CotisationURSSAF_index') . '.');

                    return $this->redirectToRoute('treso_BV_index');
                }

                $baseURSSAF = $em->getRepository(BaseURSSAF::class)->findByDate($bv->getDateDemission());
                if (null === $baseURSSAF) {
                    $this->addFlash('danger', 'Il n\'y a aucune base Urssaf définie pour cette période. 
                    Pour ajouter une base URSSAF : ' . $router->generate('treso_BaseURSSAF_index') . '.');

                    return $this->redirectToRoute('treso_BV_index');
                }
                $bv->setBaseURSSAF($baseURSSAF);

                $em->persist($bv);
                $em->flush();
                $this->addFlash('success', 'BV enregistré');

                return $this->redirectToRoute('treso_BV_index', []);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('Treso/BV/modifier.html.twig', [
            'form' => $form->createView(),
            'bv' => $bv,
        ]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_BV_voir", path="/Tresorerie/BV/{id}", methods={"GET","HEAD"})
     *
     * @param BV $bv
     *
     * @return Response
     */
    public function voir(BV $bv)
    {
        return $this->render('Treso/BV/voir.html.twig', ['bv' => $bv]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="treso_BV_supprimer", path="/Tresorerie/BV/Supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param BV $bv
     *
     * @return RedirectResponse
     */
    public function supprimer(BV $bv)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($bv);
        $em->flush();
        $this->addFlash('success', 'BV supprimé');

        return $this->redirectToRoute('treso_BV_index', []);
    }
}
