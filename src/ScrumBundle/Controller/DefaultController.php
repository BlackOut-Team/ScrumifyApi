<?php

namespace ScrumBundle\Controller;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ColumnChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Github\Api\User;
use Proxies\__CG__\TeamBundle\Entity\team;
use ScrumBundle\Entity\Projet;
use SprintBundle\Entity\Sprint;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use TeamBundle\Entity\team_user;
use TeamBundle\TeamBundle;

class DefaultController extends Controller
{
    public function  AddPAction(Request $request)
    {


        $p= new Projet();
        $duedate= new \DateTime($request->get('duedate'));
        $now = new \DateTime('now');

        if($duedate > $now) {

            $em = $this->getDoctrine()->getManager();
             $p->setCreated(new \DateTime('now'));
            $p->setMasterId($this->getUser());
            $p->setEtat(1);
            $p->setName($request->get('name'));
            $p->setDescription($request->get('description'));
            $p->setDuedate($duedate);
            $team=$em->getRepository(team::class)->find($request->get('team_id'));
            $p->setTeam($team);
            $em->persist($p);
            $em->flush($p);
                $serializer = new Serializer([new ObjectNormalizer()]);
                $formatted = $serializer->normalize($p);
                return new JsonResponse($formatted);

            }
            else {
                $serializer = new Serializer([new ObjectNormalizer()]);
                $formatted = $serializer->normalize('erreur');
                return new JsonResponse($formatted);
            }



    }

    public function archiverPAction(Request $request, Projet $project){

        $em= $this->getDoctrine()->getManager();
        $project->setEtat(0);
        $em->persist($project);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($project);
        return new JsonResponse($formatted);
    }
    public function editPAction(Request $request,  $id){
        $project = $this->getDoctrine()->getRepository(Projet::class)->find($id);
        $duedate= new \DateTime($request->get('duedate'));
        $now = $project->getCreated();

        if($duedate > $now) {

            $em = $this->getDoctrine()->getManager();

            $this->getDoctrine()->getManager()->flush();
            $project->setName($request->get('name'));
            $project->setDescription($request->get('description'));
            $project->setDuedate(new \DateTime($request->get('duedate')));
            $team = $em->getRepository(team::class)->find($request->get('team_id'));
            $project->setTeam($team);
            $em->flush($project);
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize($project);
            return new JsonResponse($formatted);
        }
        else {
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize('erreur');
            return new JsonResponse($formatted);
        }


    }

    public function  showPAction($user_id)
    {

       // $myproject=$this->getDoctrine()->getRepository(Projet::class)->findBy(['etat'=>1,'master_id'=>$this->getUser()]);
        $ownerproject=$this->getDoctrine()->getRepository(Projet::class)->findBy(['etat'=>1,'owner_id'=>$this->getUser()]);
         //$myproject=$this->getDoctrine()->getRepository(Projet::class)->getAllP($user_id);
        $all = $this->getDoctrine()->getRepository(Projet::class)->getAllP($user_id);

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize(array($all));
        return new JsonResponse($formatted);


    }
    public function desarchiverPAction(Request $request, Projet $pp){

        $em= $this->getDoctrine()->getManager();
        $pp->setEtat(1);
        $em->persist($pp);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($pp);
        return new JsonResponse($formatted);

    }
    function searchPAction(Request $request){
         $projet=new Projet();
            $Form=$this->createFormBuilder($projet)
                ->add('Name')
                ->add('Recherche',SubmitType::class,
                    ['attr'=>['formvalidate'=>'formvalidate']])
                ->getForm();
            $Form->handleRequest($request);
            if($Form->isSubmitted()){
                $projet=$this->getDoctrine()->getRepository(Projet::class)->findBy(array('name'=>$projet->getName()));
            }
            return $this->render('@Projet/Default/showProjects.html.twig',
                array('S'=>$Form->createView(),'p'=>$projet));


    }

    public function chartAction()
    {
        $em= $this->getDoctrine()->getManager();
        $pieChart = new PieChart();
        $Archive =$em->getRepository('Scrum:Projet')->findBy(['etat'=>0]);
        $Active =$em->getRepository('Scrum:Projet')->findBy(['etat'=>1]);

        $user=$em->getRepository('MainBundle:User')->findAll();

        $sizeAr = count($Archive);
        $sizeAc = count($Active);

        $size = count($user);
        $oldColumnChart = new ColumnChart();
        $oldColumnChart->getData()->setArrayToDataTable(
            [['Project', 'Project'],
                ['Archive',     $sizeAr],
                ['Active',      $sizeAc],

            ]
        );
        $pieChart->getData()->setArrayToDataTable(
            [['Task', 'Task'],
                ['Archive',     $sizeAr],
                ['Active',      $sizeAc],
                ]
        );
        $pieChart->getOptions()->setTitle('Projects');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(900);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#16CABD');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);
        $oldColumnChart->getOptions()->getLegend()->setPosition('top');
        $oldColumnChart->getOptions()->setWidth(450);
        $oldColumnChart->getOptions()->setHeight(250);

        $newColumnChart = new ColumnChart();
        $newColumnChart->getData()->setArrayToDataTable(
            [
                ['n', 'Member'],
                [ 'Number of members in team', $size]

            ]);
        $newColumnChart->setOptions($oldColumnChart->getOptions());



        return $this->render('@Scrum/Back/chart.html.twig', array(
            'oldColumnChart' => $oldColumnChart,
            'newColumnChart' => $newColumnChart,
            'piechart' => $pieChart

        ));
    }







}
