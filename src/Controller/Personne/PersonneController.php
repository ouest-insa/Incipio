<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Personne;

use App\Entity\Personne\Personne;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonneController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="MgatePersonne_annuaire", path="/annuaire", methods={"GET","HEAD"})
     *
     * @return Response
     */
    public function annuaireAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Personne::class)->findAll();

        return $this->render('Personne/Personne/annuaire.html.twig', [
            'personnes' => $entities,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="MgatePersonne_listeDiffusion", path="/listediffusion", methods={"GET","HEAD"})
     *
     * @return Response
     */
    public function listeMailAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Personne::class)->getAllPersonne();

        $cotisants = [];
        $cotisantsEtu = [];
        //Formely here (check git history if required) : membres mail management code commented.
        $nbrCotisants = count($cotisants);
        $nbrCotisantsEtu = count($cotisantsEtu);

        $listCotisants = '';
        $listCotisantsEtu = '';
        foreach ($cotisants as $nom => $mail) {
            $listCotisants .= "$nom <$mail>; ";
        }
        foreach ($cotisantsEtu as $nom => $mail) {
            $listCotisantsEtu .= "$nom <$mail>; ";
        }

        return $this->render('Personne/Personne/listeDiffusion.html.twig', [
            'personnes' => $entities,
            'cotisants' => $listCotisants,
            'cotisantsEtu' => $listCotisantsEtu,
            'nbrCotisants' => $nbrCotisants,
            'nbrCotisantsEtu' => $nbrCotisantsEtu,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="MgatePersonne_personne_supprimer", path="/personne/supprimer/{id}", methods={"HEAD","POST"})
     *
     * @param Personne $personne
     *
     * @return RedirectResponse
     */
    public function deleteAction(Personne $personne)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($personne);
        $em->flush();

        return $this->redirectToRoute('MgatePersonne_annuaire');
    }
}
