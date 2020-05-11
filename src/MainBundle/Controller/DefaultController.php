<?php

namespace MainBundle\Controller;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultController extends Controller
{

    public function indexAction
        (Request $request){

            $username = $request->query->get('username');

            $password = $request->query->get('password');


            $serializer = new Serializer([new ObjectNormalizer()]);
            $user_manager = $this->get('fos_user.user_manager');
            $factory = $this->get('security.encoder_factory');

            $user = $user_manager->findUserByUsername($username);
            if($user){
                $encoder = $factory->getEncoder($user);

                $bool = ($encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt())) ? "true" : "false";

                if($encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt())) {$formatted = $serializer->normalize(array($user));}
                else $formatted = $serializer->normalize(array("false"));
            }
            else
                $formatted = $serializer->normalize(array("false"));
            return new JsonResponse($formatted);
    }
    public function indexbackAction()
    {
        return $this->render('@Main/Default/indexback.html.twig');
    }

    public function loginAction()
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize(array(true));
        return new JsonResponse($formatted);
    }
    public function registerAction()
    {
        return $this->render('@Main/Registration/register.html.twig');
    }


    public function dashboardAction()
    {
        $em= $this->getDoctrine()->getManager();
        $pieChart = new PieChart();
        $project =$em->getRepository('ProjectApi:Projet')->findAll();
        $user =$em->getRepository('MainBundle:User')->findAll();
        $team =$em->getRepository('TeamBundle:Team')->findAll();

        $sizeP = count($project);
        $sizeU = count($user);
        $sizeT = count($team);

        $pieChart->getData()->setArrayToDataTable(
            [['Task', 'number'],
                ['Project',     $sizeP],
                ['users',      $sizeU],
                ['teams',  $sizeT],

            ]
        );
        $pieChart->getOptions()->setTitle('General informations');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(900);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#16CABD');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);

        return $this->render('@Main/Default/indexBack.html.twig', array('piechart' => $pieChart, 'sizeP'=>$sizeP , 'sizeU'=>$sizeU, 'sizeT'=>$sizeT));
    }

    public function connectAction(){
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize(array(true));
        return new JsonResponse($formatted);


    }


}
