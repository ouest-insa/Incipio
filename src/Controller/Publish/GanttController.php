<?php

namespace Mgate\PubliBundle\Controller;

use Mgate\SuiviBundle\Entity\Etude;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class GetGanttController.
 */
class GanttController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * Génère le Gantt Chart de l'étude passée en paramètre.
     *
     * @param Etude $etude project whom gantt chart should be exported
     * @param int   $width width of exported gantt
     * @param bool  $debug
     *
     * @return Response a png of project gantt chart
     * @Route(name="Mgate_publi_getgantt", path="/Documents/GetGantt/{id}/{width}", methods={"GET","HEAD"}, requirements={"id": "\d+", "width": "\d+"}, defaults={"width": "960"})
     */
    public function getGanttAction(Etude $etude, $width = 960, $debug = false)
    {
        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        /** Handle naming conventions for files. (To have a single usable version for Mgate & N7 Consulting) */
        $name = $etude->getId();
        if ($this->get('app.json_key_value_store')->exists('namingConvention')) {
            $naming_convention = $this->get('app.json_key_value_store')->get('namingConvention');

            /* Ensure $name should not contains any space character, otherwise gantt export error.*/
            if (false !== strpos($etude->getReference($naming_convention), ' ')) {
                $name = $etude->getId();
            }
        }

        //Gantt
        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGantt($etude, 'gantt');
        $chartManager->exportGantt($ob, 'gantt' . $name, $width);

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Content-disposition', 'attachment; filename="gantt' . $name . '.png"');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Expires', 0);

        $response->setContent(file_get_contents('tmp/gantt' . $name . '.png'));

        return $response;
    }
}
