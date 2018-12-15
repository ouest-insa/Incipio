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

use Mgate\TresoBundle\Entity\NoteDeFrais as NoteDeFrais;
use Mgate\TresoBundle\Form\Type\NoteDeFraisType as NoteDeFraisType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class NoteDeFraisController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="MgateTreso_NoteDeFrais_index", path="/Tresorerie/NoteDeFrais", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $nfs = $em->getRepository('MgateTresoBundle:NoteDeFrais')->findAll();

        return $this->render('MgateTresoBundle:NoteDeFrais:index.html.twig', ['nfs' => $nfs]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     *
     * @param NoteDeFrais $nf
     *
     * @return Response
     * @Route(name="MgateTreso_NoteDeFrais_voir", path="/Tresorerie/NoteDeFrais/{id}", methods={"GET","HEAD"}, requirements={"id": "\d+"})
     */
    public function voirAction(NoteDeFrais $nf)
    {
        return $this->render('MgateTresoBundle:NoteDeFrais:voir.html.twig', ['nf' => $nf]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     *
     * @param Request $request
     * @param $id
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateTreso_NoteDeFrais_ajouter", path="/Tresorerie/NoteDeFrais/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1"})
     * @Route(name="MgateTreso_NoteDeFrais_modifier", path="/Tresorerie/NoteDeFrais/Modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$nf = $em->getRepository('MgateTresoBundle:NoteDeFrais')->find($id)) {
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

                return $this->redirectToRoute('MgateTreso_NoteDeFrais_voir', ['id' => $nf->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateTresoBundle:NoteDeFrais:modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param NoteDeFrais $nf
     *
     * @return RedirectResponse
     * @Route(name="MgateTreso_NoteDeFrais_supprimer", path="/Tresorerie/NoteDeFrais/Supprimer/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function supprimerAction(NoteDeFrais $nf)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($nf);
        $em->flush();
        $this->addFlash('success', 'Note de frais supprimée');

        return $this->redirectToRoute('MgateTreso_NoteDeFrais_index', []);
    }
}
