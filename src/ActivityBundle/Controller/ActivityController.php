<?php

namespace ActivityBundle\Controller;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Entity\Meetings;
use ActivityBundle\Repository\ActivityRepository;
use FOS\UserBundle\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ActivityController extends Controller
{


    public function AfficherAction(Request $request)
    {
        $teamRepository = $this->getDoctrine()->getManager()->getRepository(Activity::class);
        $Activities=$teamRepository->findActivities($request->get('id'));

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize(array($Activities));
        return new JsonResponse($formatted);
    }
    function SupprimerAction(Request $request){
        $em=$this->getDoctrine()->getManager();
        $Activity=$em->getRepository(Activity::class)
            ->find($request->get('id'));
        $em->remove($Activity);
        $em->flush();

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize('success');
        return new JsonResponse($formatted);
    }
    public function ChangeActivityStateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $activity=$em->getRepository(Activity::class)
            ->find($request->get('id'));
        $activity->setViewed(1);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize('success');
        return new JsonResponse($formatted);
    }
   /* public function getNotifAction(Request $request){
        if ($request->isXmlHttpRequest() ) {

            //convertir entity array -> json pour l'envoyer lel ajax
            $normalizer = new ObjectNormalizer(null);
            $normalizer->setIgnoredAttributes(array('notifiableEntity'));
            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });
            $encoder = new JsonEncoder();
            $serializer = new Serializer(array($normalizer), array($encoder));


            //online user
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $notifiableRepo = $this->getDoctrine()->getManager()->getRepository('MgiletNotificationBundle:NotifiableNotification');
            $notificationList = $notifiableRepo->findAllForNotifiable($user->getId(), \MainBundle\Entity\User::class );
            $jsonContent = $serializer->serialize($notificationList, 'json');

            $response =new JsonResponse($jsonContent) ;
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


        return false ;

    }*/
}
