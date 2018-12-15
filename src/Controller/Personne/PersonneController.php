<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PersonneController extends Controller
{
    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function annuaireAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgatePersonneBundle:Personne')->findAll();

        return $this->render('MgatePersonneBundle:Personne:annuaire.html.twig', [
            'personnes' => $entities,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function listeMailAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgatePersonneBundle:Personne')->getAllPersonne();

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

        return $this->render('MgatePersonneBundle:Personne:listeDiffusion.html.twig', [
            'personnes' => $entities,
            'cotisants' => $listCotisants,
            'cotisantsEtu' => $listCotisantsEtu,
            'nbrCotisants' => $nbrCotisants,
            'nbrCotisantsEtu' => $nbrCotisantsEtu,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$entity = $em->getRepository('Mgate\PersonneBundle\Entity\Personne')->find($id)) {
            throw $this->createNotFoundException('La personne demandée n\'existe pas !');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirectToRoute('MgatePersonne_annuaire');
    }
}
