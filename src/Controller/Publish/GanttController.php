<?php

namespace App\Controller\Publish;

use App\Entity\Project\Etude;
use App\Service\Project\ChartManager;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class GetGanttController.
 */
class GanttController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="Mgate_publi_getgantt", path="/Documents/GetGantt/{id}/{width}", methods={"GET","HEAD"}, requirements={"width": "\d+"}, defaults={"width": "960"})
     * Génère le Gantt Chart de l'étude passée en paramètre.
     *
     * @param Etude                  $etude project whom gantt chart should be exported
     * @param int                    $width width of exported gantt
     * @param bool                   $debug
     * @param EtudePermissionChecker $permChecker
     * @param ChartManager           $chartManager
     *
     * @return Response a png of project gantt chart
     */
    public function getGanttAction(Etude $etude, $width = 960, $debug = false, EtudePermissionChecker $permChecker, ChartManager $chartManager)
    {
        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
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
