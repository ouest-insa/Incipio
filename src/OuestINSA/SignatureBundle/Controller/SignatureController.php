<?php

namespace OuestINSA\SignatureBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\Form\Extension\Core\Type\HiddenType;
//use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SignatureController extends Controller
{

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction()
    {
        return $this->render('OuestINSASignatureBundle:Signature:index.html.twig');
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function generateAction(){
      return $this->render('OuestINSASignatureBundle:Signature:generate.html.twig');
    }
}
