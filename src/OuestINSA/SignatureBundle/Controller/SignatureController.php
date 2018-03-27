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
        //$entities = $this->getDoctrine()->getManager()->getRepository('OuestINSASignatureBundle:Signature')->findAll();
        //$em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('MgateUserBundle:User')->findAll();
        return $this->render('OuestINSASignatureBundle:Signature:index.html.twig', ['users' => $entities]);
        //return $this->render('OuestINSASignatureBundle:Signature:index.html.twig');
        //return new Response("It works !");
    }


    public function generateAction(){
      return new Response("Work in progess");
    }

    public function signatureTempAction(){
      return $this->render('OuestINSASignatureBundle:Signature:temp.html.twig', ['users' => $entities]);
    }
}
