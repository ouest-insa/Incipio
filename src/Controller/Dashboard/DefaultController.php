<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\DashboardBundle\Controller;

use Mgate\SuiviBundle\Controller\EtudeController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\KeyValueStore\Api\KeyValueStore;

class DefaultController extends Controller
{
    public const EXPIRATION = 3600; // cache on dashboard is updated every hour

    /**
     * @Route(name="Mgate_dashboard_homepage", path="/", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $statsStore = $this->get('app.json_stats');
        if (!$statsStore->exists('expiration') ||
            ($statsStore->exists('expiration') &&
                intval($statsStore->get('expiration')) + self::EXPIRATION < time()
            )
        ) {
            $this->updateDashboardStats($statsStore);
        }
        $stats = $statsStore->getMultiple(['ca_negociation', 'ca_encours', 'ca_cloture', 'ca_facture', 'ca_paye', 'expiration']);

        return $this->render('MgateDashboardBundle:Default:index.html.twig', ['stats' => (isset($stats) ? $stats : [])]);
    }

    /**
     * @Route(name="Mgate_dashboard_search", path="/search", methods={"GET","HEAD"})
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //retrieve search
        $search = $request->query->get('q');

        $projects = $em->getRepository('MgateSuiviBundle:Etude')->searchByNom($search);
        $prospects = $em->getRepository('MgatePersonneBundle:Prospect')->searchByNom($search);
        $people = $em->getRepository('MgatePersonneBundle:Personne')->searchByNom($search);

        return $this->render('MgateDashboardBundle:Default:search.html.twig', [
            'search' => $search,
            'projects' => $projects,
            'prospects' => $prospects,
            'people' => $people,
        ]);
    }

    private function updateDashboardStats(KeyValueStore $statsStore)
    {
        $etudeRepository = $this->getDoctrine()
            ->getRepository('MgateSuiviBundle:Etude');
        $statsStore->set('ca_negociation', $etudeRepository->getCaByState(EtudeController::STATE_ID_EN_NEGOCIATION));
        $statsStore->set('ca_encours', $etudeRepository->getCaByState(EtudeController::STATE_ID_EN_COURS));
        $statsStore->set('ca_cloture', $etudeRepository->getCaByState(EtudeController::STATE_ID_TERMINEE, date('Y')));

        $factureRepository = $this->getDoctrine()->getRepository('MgateTresoBundle:Facture');
        $statsStore->set('ca_facture', $factureRepository->getCAFacture(date('Y')));
        $statsStore->set('ca_paye', $factureRepository->getCAFacture(date('Y'), true));

        $statsStore->set('expiration', time());
    }
}
