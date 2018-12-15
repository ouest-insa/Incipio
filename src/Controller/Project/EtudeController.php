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
use Mgate\SuiviBundle\Form\Type\EtudeType;
use Mgate\SuiviBundle\Form\Type\SuiviEtudeType;
use Mgate\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EtudeController extends Controller
{
    const STATE_ID_EN_NEGOCIATION = 1;

    const STATE_ID_EN_COURS = 2;

    const STATE_ID_EN_PAUSE = 3;

    const STATE_ID_TERMINEE = 4;

    const STATE_ID_AVORTEE = 5;

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_etude_homepage", path="/suivi/", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $MANDAT_MAX = $this->get('Mgate.etude_manager')->getMaxMandat();
        $MANDAT_MIN = $this->get('Mgate.etude_manager')->getMinMandat();

        $em = $this->getDoctrine()->getManager();

        //Etudes En Négociation : stateID = 1
        $etudesEnNegociation = $em->getRepository('MgateSuiviBundle:Etude')->getPipeline(['stateID' => self::STATE_ID_EN_NEGOCIATION], ['mandat' => 'DESC', 'num' => 'DESC']);

        //Etudes En Cours : stateID = 2
        $etudesEnCours = $em->getRepository('MgateSuiviBundle:Etude')->getPipeline(['stateID' => self::STATE_ID_EN_COURS], ['mandat' => 'DESC', 'num' => 'DESC']);

        //Etudes en pause : stateID = 3
        $etudesEnPause = $em->getRepository('MgateSuiviBundle:Etude')->getPipeline(['stateID' => self::STATE_ID_EN_PAUSE], ['mandat' => 'DESC', 'num' => 'DESC']);

        /**
         * @Route(name="MgateSuivi_etude_ajax", path="/suivi/get", methods={"GET","HEAD"})
         */
        //Etudes Terminees et Avortees Chargée en Ajax dans getEtudesAsyncAction
        //On push des arrays vides pour avoir les menus déroulants
        $etudesTermineesParMandat = [];
        $etudesAvorteesParMandat = [];

        for ($i = $MANDAT_MIN; $i <= $MANDAT_MAX; ++$i) {
            array_push($etudesTermineesParMandat, []);
            array_push($etudesAvorteesParMandat, []);
        }

        $anneeCreation = $this->get('app.json_key_value_store')->get('anneeCreation');

        return $this->render('MgateSuiviBundle:Etude:index.html.twig', [
            'etudesEnNegociation' => $etudesEnNegociation,
            'etudesEnCours' => $etudesEnCours,
            'etudesEnPause' => $etudesEnPause,
            'etudesTermineesParMandat' => $etudesTermineesParMandat,
            'etudesAvorteesParMandat' => $etudesAvorteesParMandat,
            'anneeCreation' => $anneeCreation,
            'mandatMax' => $MANDAT_MAX,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getEtudesAsyncAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ('GET' == $request->getMethod()) {
            $mandat = intval($request->query->get('mandat'));
            $stateID = intval($request->query->get('stateID'));

            if (!empty($mandat) && !empty($stateID)) { // works because state & mandat > 0
                $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findBy(['stateID' => $stateID, 'mandat' => $mandat], ['num' => 'DESC']);

                if (self::STATE_ID_TERMINEE == $stateID) {
                    return $this->render('MgateSuiviBundle:Etude:Tab/EtudesTerminees.html.twig', ['etudes' => $etudes]);
                } elseif (self::STATE_ID_AVORTEE == $stateID) {
                    return $this->render('MgateSuiviBundle:Etude:Tab/EtudesAvortees.html.twig', ['etudes' => $etudes]);
                }
            }
        }

        return $this->render('MgateSuiviBundle:Etude:Tab/EtudesAvortees.html.twig', [
            'etudes' => null,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     *
     * @return Response
     * @Route(name="MgateSuivi_state", path="/suivi/suivi/state/", methods={"PUT"})
     */
    public function stateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $stateDescription = !empty($request->request->get('state')) ? $request->request->get('state') : '';
        $stateID = !empty($request->request->get('id')) ? intval($request->request->get('id')) : 0;
        $etudeID = !empty($request->request->get('etude')) ? intval($request->request->get('etude')) : 0;

        if (!$etude = $em->getRepository('Mgate\SuiviBundle\Entity\Etude')->find($etudeID)) {
            throw $this->createNotFoundException('L\'étude n\'existe pas !');
        } else {
            $etude->setStateDescription($stateDescription);
            $etude->setStateID($stateID);
            $em->persist($etude);
            $em->flush();
        }

        return new Response($stateDescription);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route(name="MgateSuivi_etude_ajouter", path="/suivi/etude/ajouter", methods={"GET","HEAD","POST"})
     */
    public function addAction(Request $request)
    {
        $etude = new Etude();

        $etude->setMandat($this->get('Mgate.etude_manager')->getMaxMandat());
        $etude->setNum($this->get('Mgate.etude_manager')->getNouveauNumero());
        $etude->setFraisDossier($this->get('Mgate.etude_manager')->getDefaultFraisDossier());
        $etude->setPourcentageAcompte($this->get('Mgate.etude_manager')->getDefaultPourcentageAcompte());

        $user = $this->getUser();
        if (is_object($user) && $user instanceof User) {
            $etude->setSuiveur($user->getPersonne());
        }

        $form = $this->createForm(EtudeType::class, $etude);
        $em = $this->getDoctrine()->getManager();

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if ((!$etude->isKnownProspect() && !$etude->getNewProspect()) || !$etude->getProspect()) {
                    $this->addFlash('danger', 'Vous devez définir un prospect');

                    return $this->render('MgateSuiviBundle:Etude:ajouter.html.twig', ['form' => $form->createView()]);
                } elseif (!$etude->isKnownProspect()) {
                    $etude->setProspect($etude->getNewProspect());
                }

                $em->persist($etude);
                $em->flush();
                $this->addFlash('success', 'Etude ajoutée');

                if ($request->get('ap')) {
                    return $this->redirectToRoute('MgateSuivi_ap_rediger', ['id' => $etude->getId()]);
                } else {
                    return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
                }
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateSuiviBundle:Etude:ajouter.html.twig', [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Etude $etude
     *
     * @return Response
     * @Route(name="MgateSuivi_etude_voir", path="/suivi/etude/{nom}", methods={"GET","HEAD"}, requirements={"numero": "\d+"})
     */
    public function voirAction(Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        //get contacts clients
        $clientContacts = $em->getRepository('MgateSuiviBundle:ClientContact')->getByEtude($etude, ['date' => 'desc']);

        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGantt($etude, 'suivi');

        $formSuivi = $this->createForm(SuiviEtudeType::class, $etude);

        return $this->render('MgateSuiviBundle:Etude:voir.html.twig', [
            'etude' => $etude,
            'formSuivi' => $formSuivi->createView(),
            'chart' => $ob,
            'clientContacts' => $clientContacts,
            /* 'delete_form' => $deleteForm->createView(),  */]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude
     *
     * @return RedirectResponse|Response
     * @Route(name="MgateSuivi_etude_modifier", path="/suivi/etude/modifier/{nom}", methods={"GET","HEAD","POST"}, requirements={"numero": "\d+"})
     */
    public function modifierAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(EtudeType::class, $etude);

        $deleteForm = $this->createDeleteForm($etude);
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if ((!$etude->isKnownProspect() && !$etude->getNewProspect()) || !$etude->getProspect()) {
                    $this->addFlash('danger', 'Vous devez définir un prospect');

                    return $this->render('MgateSuiviBundle:Etude:modifier.html.twig', [
                        'form' => $form->createView(),
                        'etude' => $etude,
                        'delete_form' => $deleteForm->createView(),
                    ]);
                } elseif (!$etude->isKnownProspect()) {
                    $etude->setProspect($etude->getNewProspect());
                }

                $em->persist($etude);
                $em->flush();
                $this->addFlash('success', 'Etude modifiée');

                if ($request->get('ap')) {
                    return $this->redirectToRoute('MgateSuivi_ap_rediger', ['id' => $etude->getId()]);
                } else {
                    return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
                }
            } else {
                $errors = $this->get('validator')->validate($etude);
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getPropertyPath() . ' : ' . $error->getMessage());
                }
            }
        }

        return $this->render('MgateSuiviBundle:Etude:modifier.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Etude   $etude
     * @param Request $request
     *
     * @return RedirectResponse
     * @Route(name="MgateSuivi_etude_supprimer", path="/suivi/etude/supprimer/{nom}", methods={"GET","HEAD","POST"}, requirements={"numero": "\d+"})
     */
    public function deleteAction(Etude $etude, Request $request)
    {
        $form = $this->createDeleteForm($etude);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
                throw new AccessDeniedException('Cette étude est confidentielle');
            }

            $em->remove($etude);
            $em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Etude supprimée');
        }

        return $this->redirectToRoute('MgateSuivi_etude_homepage');
    }

    private function createDeleteForm(Etude $etude)
    {
        return $this->createFormBuilder(['id' => $etude->getId()])
            ->add('id', HiddenType::class)
            ->getForm();
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     *
     * @return Response
     * @Route(name="MgateSuivi_etude_suivi", path="/suivi/etudes/suivi", methods={"GET","HEAD"})
     */
    public function suiviAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $MANDAT_MAX = 10;

        $etudesParMandat = [];

        for ($i = 1; $i < $MANDAT_MAX; ++$i) {
            array_push($etudesParMandat, $em->getRepository('MgateSuiviBundle:Etude')->findBy(['mandat' => $i], ['num' => 'DESC']));
        }

        //WARN
        /* Création d'un form personalisé sans classes (Symfony Forms without Classes)
         *
         * Le problème qui se pose est de savoir si les données reçues sont bien destinées aux bonnes études
         * Si quelqu'un ajoute une étude ou supprime une étude pendant la soumission de se formulaire, c'est la cata
         * tout se décale de 1 étude !!
         * J'ai corrigé ce bug en cas d'ajout d'une étude. Les changements sont bien sauvegardés !!
         * Mais cette page doit être rechargée et elle l'est automatiquement. (Si js est activé !)
         * bref rien de bien fracassant. Solution qui se doit d'être temporaire bien que fonctionnelle !
         * Cependant en cas de suppression d'une étude, chose qui n'arrive pas tous les jours, les données seront perdues !!
         */
        $etudesEnCours = [];

        $NbrEtudes = 0;
        foreach ($etudesParMandat as $etudesInMandat) {
            $NbrEtudes += count($etudesInMandat);
        }

        $form = $this->createFormBuilder();

        if ($this->get('app.json_key_value_store')->exists('namingConvention')) {
            $namingConvention = $this->get('app.json_key_value_store')->get('namingConvention');
        } else {
            $namingConvention = 'id';
        }
        $id = 0;
        foreach (array_reverse($etudesParMandat) as $etudesInMandat) {
            /** @var Etude $etude */
            foreach ($etudesInMandat as $etude) {
                $form = $form->add((string) (2 * $id), HiddenType::class,
                    ['label' => 'refEtude',
                        'data' => $etude->getReference($namingConvention), ]
                )
                    ->add((string) (2 * $id + 1), TextareaType::class, ['label' => $etude->getReference($namingConvention),
                        'required' => false, 'data' => $etude->getStateDescription(), ]);
                ++$id;
                if (self::STATE_ID_EN_COURS == $etude->getStateID()) {
                    array_push($etudesEnCours, $etude);
                }
            }
        }
        $form = $form->getForm();

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            $data = $form->getData();

            $id = 0;
            foreach (array_reverse($etudesParMandat) as $etudesInMandat) {
                foreach ($etudesInMandat as $etude) {
                    if ($data[2 * $id] == $etude->getReference($namingConvention)) {
                        if ($data[2 * $id] != $etude->getStateDescription()) {
                            $etude->setStateDescription($data[2 * $id + 1]);
                            $em->persist($etude);
                            ++$id;
                        }
                    } else {
                        echo '<script>location.reload();</script>';
                    }
                }
            }
            $em->flush();
        }

        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGanttSuivi($etudesEnCours);

        return $this->render('MgateSuiviBundle:Etude:suiviEtudes.html.twig', [
            'etudesParMandat' => $etudesParMandat,
            'form' => $form->createView(),
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="MgateSuivi_etude_suiviQualite", path="/suivi/etudes/suiviQualite", methods={"GET","HEAD"})
     */
    public function suiviQualiteAction()
    {
        $em = $this->getDoctrine()->getManager();

        $etudesEnCours = $em->getRepository('MgateSuiviBundle:Etude')->findBy(['stateID' => self::STATE_ID_EN_COURS], ['mandat' => 'DESC', 'num' => 'DESC']);
        $etudesTerminees = $em->getRepository('MgateSuiviBundle:Etude')->findBy(['stateID' => self::STATE_ID_TERMINEE], ['mandat' => 'DESC', 'num' => 'DESC']);
        $etudes = array_merge($etudesEnCours, $etudesTerminees);

        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGanttSuivi($etudes);

        return $this->render('MgateSuiviBundle:Etude:suiviQualite.html.twig', [
            'etudesEnCours' => $etudesEnCours,
            'etudesTerminees' => $etudesTerminees,
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     * @param Etude   $etude
     *
     * @return JsonResponse
     * @Route(name="MgateSuivi_etude_suivi_update", path="/suivi/suivi/update/{id}", methods={"POST"})
     */
    public function suiviUpdateAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $formSuivi = $this->createForm(SuiviEtudeType::class, $etude);
        if ('POST' == $request->getMethod()) {
            $formSuivi->handleRequest($request);

            if ($formSuivi->isValid()) {
                $em->persist($etude);
                $em->flush();

                $return = ['responseCode' => 200, 'msg' => 'ok'];
            } else {
                $return = ['responseCode' => 412, 'msg' => 'Erreur:' . $formSuivi->getErrors(true, false)];
            }
        }

        return new JsonResponse($return); //make sure it has the correct content type
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param $id
     *
     * @return Response
     * @Route(name="MgateSuivi_vu_ca", path="/suivi/ca/{id}", methods={"GET","HEAD"}, defaults={"id": "-1"})
     */
    public function vuCAAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id > 0) {
            $etude = $em->getRepository('MgateSuiviBundle:Etude')->find($id);
        } else {
            $etude = $em->getRepository('MgateSuiviBundle:Etude')->findOneBy(['stateID' => self::STATE_ID_EN_COURS]);
        }

        if (null === $etude) {
            $etude = $em->getRepository('MgateSuiviBundle:Etude')->findOneBy(['stateID' => self::STATE_ID_EN_NEGOCIATION]);
        }

        if (null === $etude) {
            throw $this->createNotFoundException('Vous devez avoir au moins une étude de créée pour accéder à cette page.');
        }

        //Etudes En Négociation : stateID = 1
        $etudesDisplayList = $em->getRepository('MgateSuiviBundle:Etude')->getTwoStates([self::STATE_ID_EN_NEGOCIATION,
            self::STATE_ID_EN_COURS, ], ['mandat' => 'ASC', 'num' => 'ASC']);

        if (!in_array($etude, $etudesDisplayList)) {
            throw $this->createNotFoundException('Etude incorrecte');
        }

        /* pagination management */
        $currentEtudeId = array_search($etude, $etudesDisplayList);
        $nextId = min(count($etudesDisplayList), $currentEtudeId + 1);
        $previousId = max(0, $currentEtudeId - 1);

        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGantt($etude, 'suivi');

        return $this->render('MgateSuiviBundle:Etude:vuCA.html.twig', [
            'etude' => $etude,
            'chart' => $ob,
            'nextID' => (null !== $etudesDisplayList[$nextId] ? $etudesDisplayList[$nextId]->getId() : 0),
            'prevID' => (null !== $etudesDisplayList[$previousId] ? $etudesDisplayList[$previousId]->getId() : 0),
            'etudesDisplayList' => $etudesDisplayList,
        ]);
    }
}
