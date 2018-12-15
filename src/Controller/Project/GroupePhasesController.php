<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Controller;

use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Entity\GroupePhases;
use Mgate\SuiviBundle\Form\Type\GroupesPhasesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GroupePhasesController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateSuivi_groupes_modifier", path="/suivi/groupes/modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(GroupesPhasesType::class, $etude);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if ($request->get('add')) {
                    $groupeNew = new GroupePhases();
                    $groupeNew->setNumero(count($etude->getGroupes()));
                    $groupeNew->setTitre('Titre')->setDescription('Description');
                    $groupeNew->setEtude($etude);
                    $etude->addGroupe($groupeNew);
                    $message = 'Groupe ajouté';
                }

                $em->persist($etude); // persist $etude / $form->getData()
                $em->flush();
                $this->addFlash('success', isset($message) ? $message : 'Groupes modifiés');

                return $this->redirectToRoute('MgateSuivi_groupes_modifier', ['id' => $etude->getId()]);
            }

            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateSuiviBundle:GroupePhases:modifier.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
        ]);
    }
}
