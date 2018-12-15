<?php

namespace Mgate\PubliBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ImportController extends Controller
{
    const AVAILABLE_FORMATS = ['Siaje Etudes'];

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response Display an upload form for a csv resources from other crm.
     *                                                    Display an upload form for a csv resources from other crm
     * @Route(name="Mgate_publi_import", path="/Documents/import", methods={"GET","HEAD","POST"})
     */
    public function indexAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder([])->add('import_method', ChoiceType::class, ['label' => 'Type du fichier',
                'required' => true,
                'choices' => $this::AVAILABLE_FORMATS,
                'choice_label' => function ($value) {
                    return $value;
                },
                'expanded' => true,
                'multiple' => false, ]
        )
            ->add('file', FileType::class, ['label' => 'Fichier', 'required' => true, 'attr' => ['cols' => '100%', 'rows' => 5]])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ('Siaje Etudes' == $form->get('import_method')->getData()) {
                $data = $form->getData();

                // Création d'un fichier temporaire
                $file = $data['file'];

                $siajeImporter = $this->get('Mgate.import.siaje_etude');

                $results = $siajeImporter->run($file);

                $request->getSession()->getFlashBag()->add('success', 'Document importé. ' . $results['inserted_projects'] . ' études créées, ' . $results['inserted_prospects'] . ' prospects créés');

                return $this->redirectToRoute('Mgate_publi_import');
            }
        }

        return $this->render('MgatePubliBundle:Import:index.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param $number integer id of service as stated in $this::AVAILABLE_FORMATS
     * Return an html snippet of how csv should be formatted to match import
     *
     * @return JsonResponse an array containing expected headers
     * @Route(name="Mgate_publi_import_format", path="/Documents/import/format/{service_number}", methods={"GET","HEAD"})
     */
    public function ajaxExpectedFormatAction($service_number)
    {
        if ($service_number < count($this::AVAILABLE_FORMATS)) {
            if ('Siaje Etudes' == $this::AVAILABLE_FORMATS[$service_number]) {
                $siajeImporter = $this->get('Mgate.import.siaje_etude');

                return new JsonResponse($siajeImporter->expectedFormat());
            }
        }

        return new JsonResponse(null);
    }
}
