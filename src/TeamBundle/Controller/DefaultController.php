<?php

namespace TeamBundle\Controller;

use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\TextType;
use FOS\UserBundle\Event\FormEvent;
use MainBundle\Entity\User;
use Swift_Message;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use TeamBundle\Entity\team_user;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class DefaultController extends Controller
{
    public function affecterAction(Request $request, $id){

        $team= $this->getDoctrine()->getRepository('TeamBundle:team')->find($id);
        $users= $this->getDoctrine()->getRepository('MainBundle:User')->findAll();
        $user=new User();
        $Form=$this->createFormBuilder($user)
            ->add('Email',EmailType::class,
                ['attr'=>['placeholder'=>'Invite user to scrumify by Email']])
            ->add('Envoyer',SubmitType::class,
                ['attr'=>['formvalidate'=>'formvalidate']])
            ->getForm();

        $Form->handleRequest($request);

        if ($Form->isSubmitted()) {


            foreach ($users as $u) {
                if ($user->getEmail() == $u->getEmail()) {
                    return $this->render('@Team/team/existedeja.html.twig', array("id" => $id, "users" => $users));

                } else {
                    $message = Swift_Message::newInstance()
                        ->setSubject('Affectation au team sur la plateforme Scrumify')
                        ->setFrom('scrumify.application@gmail.com')
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->renderView('@MyAppMail/Mail/mail1.html.twig',
                                array('team' => $team->getName(), 'text/html')));
                    $this->get('mailer')->send($message);
                }
            }

        }


        return $this->render('@Team/Default/affMembre.html.twig',array("id" => $id,"users"=>$users,'f'=>$Form->createView()));

    }


    public function  displayUsersAction(){

        $users= $this->getDoctrine()->getRepository('MainBundle:User')->findAll();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($users);
        return new JsonResponse($formatted);

    }
    public function affecterUserTAction(Request $request){



        $user= $this->getDoctrine()->getRepository('MainBundle:User')->find($request->get('user_id'));
        $team= $this->getDoctrine()->getRepository('TeamBundle:team')->find($request->get('team_id'));

        $con3 = $this->getDoctrine()->getRepository('TeamBundle:team_user')->findBy(array('teamId' => $request->get('user_id'),'userId'=>$request->get('user_id')));

        if ( $con3 != null) {

           // return $this->render('@Team/team/ajout1.html.twig', array("id" => $request->get('user_id'), "users" => $request->get('user_id')));
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize("existe");
            return new JsonResponse($formatted);
        }
        else {
            if ($request->get('role') == 3) {
                $con2 = $this->getDoctrine()->getRepository('TeamBundle:team_user')->findBy(array('teamId' => $request->get('team_id'), 'userId' => $request->get('user_id'), 'role' => 3));
                if ($con2 == null) {
                    //envoyer mail notif
                    $message = Swift_Message::newInstance()
                        ->setSubject('Affectation au team sur la plateforme Scrumify : Product owner')
                        ->setFrom('scrumify.application@gmail.com', 'Scrumify Team')
                        ->setTo($user->getEmail())
                        ->setContentType("text/html")
                        ->setBody(
                            $this->renderView('@Team/team/mail.html.twig',
                                array('team' => $team->getName(),'user'=> $user->getUsername()  )));

                    $this->get('mailer')->send($message);

                    // affectation le user dans l'equipe
                    $aff = new team_user();
                    $aff->setTeamId($team);
                    $aff->setUserId($user);
                    $aff->setRole($request->get('role'));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($aff);
                    $em->flush();

                    $serializer = new Serializer([new ObjectNormalizer()]);
                    $formatted = $serializer->normalize("success");
                    return new JsonResponse($formatted);
                    // return $this->render('@Team/team/ajoutTeam.html.twig', array("id" => $team_id, "users" => $users));
                    //return $this->render('@Team/team/success.html.twig');
                    //return true;

                } else {
                    $serializer = new Serializer([new ObjectNormalizer()]);
                    $formatted = $serializer->normalize("erreur");
                    return new JsonResponse($formatted);
                }
            } else if ($request->get('role')  == 2) {
                $con1 = $this->getDoctrine()->getRepository('TeamBundle:team_user')->findBy(array('teamId' => $request->get('team_id') , 'userId' => $request->get('user_id') , 'role' => 2));


                //envoyer mail notif
                $message = Swift_Message::newInstance()
                    ->setSubject('Affectation au team sur la plateforme Scrumify : developer')
                    ->setFrom('scrumify.application@gmail.com', 'Scrumify Team')
                    ->setTo($user->getEmail())
                    ->setContentType("text/html")
                    ->setBody(
                        $this->renderView('@Team/team/mail.html.twig',
                            array('team' => $team->getName(), 'user'=> $user->getUsername() )));

                $this->get('mailer')->send($message);

                // affectation le user dans l'equipe
                $aff = new team_user();
                $aff->setTeamId($team);
                $aff->setUserId($user);
                $aff->setRole($request->get('role') );
                $em = $this->getDoctrine()->getManager();
                $em->persist($aff);
                $em->flush();

                $serializer = new Serializer([new ObjectNormalizer()]);
                $formatted = $serializer->normalize("success");
                return new JsonResponse($formatted);
                //  return $this->render('@Team/team/ajoutTeam.html.twig', array("id" => $team_id, "users" => $users));
              //  return $this->render('@Team/team/success.html.twig');
                //return true;
            }
            else
            {
                //return $this->render('@Team/team/error.html.twig');
                //return false;
                $serializer = new Serializer([new ObjectNormalizer()]);
                $formatted = $serializer->normalize("erreur");
                return new JsonResponse($formatted);
            }

        }





    }
}
