<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Project;

use App\Entity\Project\Mission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MissionController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_mission_avancement", path="/suivi/missions/avancement/{id}", methods={"PUT"})
     *
     * @param Mission $mission
     * @param Request $request
     *
     * @return Response
     */
    public function avancement(Mission $mission, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $avancement = !empty($request->request->get('avancement')) ? intval($request->request->get('avancement')) : 0;

        $mission->setAvancement($avancement);
        $em->persist($mission);
        $em->flush();

        return new Response($avancement);
    }
}
