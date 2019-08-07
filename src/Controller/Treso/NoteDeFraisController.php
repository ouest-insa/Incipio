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

use App\Entity\Treso\NoteDeFrais;
use App\Form\Treso\NoteDeFraisType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NoteDeFraisController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_NoteDeFrais_index", path="/Tresorerie/NoteDeFrais", methods={"GET","HEAD"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $nfs = $em->getRepository(NoteDeFrais::class)->findAll();

        return $this->render('Treso/NoteDeFrais/index.html.twig', ['nfs' => $nfs]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_NoteDeFrais_ajouter", path="/Tresorerie/NoteDeFrais/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1"})
     * @Route(name="treso_NoteDeFrais_modifier", path="/Tresorerie/NoteDeFrais/Modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     * @param         $id
     *
     * @return RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function modifier(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$nf = $em->getRepository(NoteDeFrais::class)->find($id)) {
            $nf = new NoteDeFrais();
            $now = new \DateTime('now');
            $nf->setDate($now);
        }

        $form = $this->createForm(NoteDeFraisType::class, $nf);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                foreach ($nf->getDetails() as $nfd) {
                    $nfd->setNoteDeFrais($nf);
                }
                $em->persist($nf);
                $em->flush();
                $this->addFlash('success', 'Note de frais enregistrée');

                return $this->redirectToRoute('treso_NoteDeFrais_voir', ['id' => $nf->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('Treso/NoteDeFrais/modifier.html.twig', [
            'form' => $form->createView(),
            'nf' => $nf,
        ]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_NoteDeFrais_voir", path="/Tresorerie/NoteDeFrais/{id}", methods={"GET","HEAD"})
     *
     * @param NoteDeFrais $nf
     *
     * @return Response
     */
    public function voir(NoteDeFrais $nf)
    {
        return $this->render('Treso/NoteDeFrais/voir.html.twig', ['nf' => $nf]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="treso_NoteDeFrais_supprimer", path="/Tresorerie/NoteDeFrais/Supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param NoteDeFrais $nf
     *
     * @return RedirectResponse
     */
    public function supprimer(NoteDeFrais $nf)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($nf);
        $em->flush();
        $this->addFlash('success', 'Note de frais supprimée');

        return $this->redirectToRoute('treso_NoteDeFrais_index', []);
    }
}
