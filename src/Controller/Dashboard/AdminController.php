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

use Mgate\DashboardBundle\Form\Type\AdminParamType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route(name="Mgate_dashboard_parameters_admin", path="/parameters/admin", methods={"GET","HEAD","POST"})
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(AdminParamType::class);

        $json_key_value_store = $this->get('app.json_key_value_store');

        $keys = $json_key_value_store->keys();

        foreach ($keys as $key) {
            $form->get($key)->setData($json_key_value_store->get($key));
        }

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $form_fields = $form->getData();
                foreach ($form_fields as $key => $value) {
                    $json_key_value_store->set($key, $value);
                }
                $this->addFlash('success', 'Paramètres mis à jour');
            }

            return $this->redirectToRoute('Mgate_dashboard_parameters_admin');
        }

        return $this->render('MgateDashboardBundle:Admin:index.html.twig',
            ['form' => $form->createView()]
        );
    }
}
