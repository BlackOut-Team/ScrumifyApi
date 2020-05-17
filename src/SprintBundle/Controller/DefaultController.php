<?php

namespace SprintBundle\Controller;

use DateTime;
use ScrumBundle\Entity\Projet;
use SprintBundle\Entity\Sprint;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('@Sprint/Default/index.html.twig');
    }
    public function  AddSAction(Request $request, Projet $projet)
    {



        $s= new Sprint();
        try {
            $duedate = new DateTime($request->get('duedate'));
        } catch (\Exception $e) {
        }
        $now = new DateTime('now');

        if($duedate > $now) {

            $em = $this->getDoctrine()->getManager();
            $s->setProject($projet);
            $s->setEtat(1);
            $s->setCreated(new DateTime('now'));
            $em->persist($s);
            $s->setName($request->get('name'));
            $s->setDescription($request->get('description'));
            $s->setDuedate($duedate);
            $em->flush($s);

            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize($s);
            return new JsonResponse($formatted);
        }
        else {
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize('erreur');
            return new JsonResponse($formatted);
        }



    }

    public function archiverSAction(Request $request,Sprint $sprint){

        $em= $this->getDoctrine()->getManager();
        $sprint->setProject($sprint->getProject());
        $sprint->setEtat(0);
        $em->persist($sprint);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($sprint);
        return new JsonResponse($formatted);
    }
    public function editSAction(Request $request, $id){
        $sprint = $this->getDoctrine()->getRepository(Sprint::class)->find($id);
        $duedate= new \DateTime($request->get('duedate'));
        $now = $sprint->getCreated();

        if($duedate > $now) {
            $em = $this->getDoctrine()->getManager();

            $this->getDoctrine()->getManager()->flush();
            $sprint->setName($request->get('name'));
            $sprint->setDescription($request->get('description'));
            $sprint->setDuedate(new \DateTime($request->get('duedate')));
            $em->flush($sprint);
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize($sprint);
            return new JsonResponse($formatted);
        }
        else {
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize('erreur');
            return new JsonResponse($formatted);
        }

    }

    public function  showSprintsAction(Projet $projet){

        $sprint=$this->getDoctrine()->getRepository(Sprint::class)->findBy(array('project'=>$projet->getId(),'etat'=>1));

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize(array($sprint));
        return new JsonResponse($formatted);

    }

}
