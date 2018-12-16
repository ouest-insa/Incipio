<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Treso;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UrssafController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="Mgate_treso_urssaf", path="/Tresorerie/urssaf/{year}/{month}", methods={"GET","HEAD","POST"}, defaults={"year": "", "month": ""})
     *
     * @param Request $request
     * @param null    $year
     * @param null    $month
     *
     * @return RedirectResponse|Response
     */
    public function indexAction(Request $request, $year = null, $month = null)
    {
        $em = $this->getDoctrine()->getManager();

        $defaultData = ['message' => 'Type your message here'];
        $form = $this->createFormBuilder($defaultData)
            ->add('date', DateType::class, ['label' => 'Missions commencées avant le :', 'required' => true, 'widget' => 'single_text', 'data' => date_create(), 'format' => 'dd/MM/yyyy'])
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                return $this->redirectToRoute('Mgate_treso_urssaf', ['year' => $data['date']->format('Y'),
                    'month' => $data['date']->format('m'),
                ]);
            }
        }

        if (null === $year || null === $month) {
            $date = new \DateTime('now');
        } else {
            $date = new \DateTime();
            $date->setDate($year, $month, 01);
        }

        $RMs = $em->getRepository('MgateSuiviBundle:Mission')->getMissionsBeginBeforeDate($date);

        return $this->render('Treso/Urssaf/index.html.twig', ['form' => $form->createView(), 'RMs' => $RMs]);
    }
}
