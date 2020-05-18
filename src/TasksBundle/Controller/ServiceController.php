<?php

namespace TasksBundle\Controller;

use Elasticsearch\Endpoints\Cat\Tasks;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ServiceController extends Controller
{
    public function showAction()
    {
        $task = $this->getDoctrine()->getManager()->getRepository('TasksBundle:Tasks')->findby(['etat'=>0]);
        $datas = array();
        foreach ($task as $key => $task){
            $datas[$key]['id'] = $task->getId();
            $datas[$key]['priority'] = $task->getId();
            $datas[$key]['title'] = $task->getTitle();
            $datas[$key]['description'] = $task->getDescription();
            $datas[$key]['status'] = $task->getStatus();
            #$datas[$key]['Categorie'] = $col->getNomcategorie()->getCategorie();
        }
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($datas);
        return new JsonResponse($formatted);
    }
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tasks = new \TasksBundle\Entity\Tasks();
        $tasks->setEtat(0);

        //$tasks->setId($request->get('idT'));
        $tasks->setTitle($request->get('title'));
        $tasks->setDescription($request->get('description'));
        $tasks->setCreated(new \DateTime('now'));
        $tasks->setStatus("Todo");
        $tasks->setUpdated(new \DateTime('now'));
        $tasks->setFinished(new \DateTime('now'));
        $tasks->setPriority($request->get('Priority'));
        $userstory= $this->getDoctrine()->getManager()->getRepository('UserstoryBundle:userstory')->find(1);
        $tasks->setUserstory($userstory);

        $a = $em->getRepository('MainBundle:User')->find($request->get('user'));
        $user= $this->getDoctrine()->getManager()->getRepository('MainBundle:User')->findOneBy(['username'=>$a->getUsername()]);
        $usersToAffect =$em->getRepository('MainBundle:User')->findBy(['username'=>'']);
        array_push($usersToAffect,$user);
        $tasks->setUser($usersToAffect);

        $em->persist($tasks);
        $em->flush();

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($tasks);
        return new JsonResponse($formatted);
    }

    public function editAction(Request $request, $Id){
        $em=$this->getDoctrine()->getManager();
        $find=  $this->getDoctrine()->getManager()->getRepository('TasksBundle:Tasks')->find($Id);

        $find->setTitle($request->get('title'));
        $find->setDescription($request->get('description'));

        $em->persist($find);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($find);
        return new JsonResponse($formatted);
    }


    public function archiveAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $find=  $this->getDoctrine()->getManager()->getRepository('TasksBundle:Tasks')->find($id);

        $find->setEtat(1);


        $em->persist($find);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($find);
        return new JsonResponse($formatted);
    }


    public function SupprimerAction($Id)
    {
        $em=$this->getDoctrine()->getManager();
        $find=  $this->getDoctrine()->getManager()->getRepository('TasksBundle:Tasks')->findBy(array('idT'=>$Id));
        foreach ($find as $col) {
            $em->remove($col);
        }
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($find);
        return new JsonResponse($formatted);
    }


}
