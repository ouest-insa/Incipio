<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Dashboard;

use App\Controller\Project\EtudeController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Webmozart\KeyValueStore\Api\KeyValueStore;

class DashboardController extends AbstractController
{
    public const EXPIRATION = 3600; // cache on dashboard is updated every hour

    public $statsStore;

    public function __construct(KeyValueStore $statsStore)
    {
        $this->statsStore = $statsStore;
    }

    /**
     * @Route(name="dashboard_homepage", path="/", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        if (!$this->statsStore->exists('expiration') ||
            ($this->statsStore->exists('expiration') &&
                intval($this->statsStore->get('expiration')) + self::EXPIRATION < time()
            )
        ) {
            $this->updateDashboardStats($this->statsStore);
        }
        $stats = $this->statsStore->getMultiple(['ca_negociation', 'ca_encours', 'ca_cloture', 'ca_facture', 'ca_paye', 'expiration']);

        return $this->render('Dashboard/Default/index.html.twig', ['stats' => (isset($stats) ? $stats : [])]);
    }

    /**
     * @Route(name="dashboard_search", path="/search", methods={"GET","HEAD"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //retrieve search
        $search = $request->query->get('q');

        $projects = $em->getRepository('MgateSuiviBundle:Etude')->searchByNom($search);
        $prospects = $em->getRepository('MgatePersonneBundle:Prospect')->searchByNom($search);
        $people = $em->getRepository('MgatePersonneBundle:Personne')->searchByNom($search);

        return $this->render('Dashboard/Default/search.html.twig', [
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
