<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MainBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use MainBundle\Entity\User;
use ScrumBundle\Entity\Projet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Controller managing the user profile.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ProfileController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $userManager;

    public function __construct(EventDispatcherInterface $eventDispatcher, FactoryInterface $formFactory, UserManagerInterface $userManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
    }

    /**
     * Show the user.
     */
    public function showAction(Request $request)
    {
        $serializer = new Serializer([new ObjectNormalizer()]);

        $id = $request->get('id');
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $formatted = $serializer->normalize(array($user));
        return new JsonResponse($formatted);


    }

    /**
     * Edit the user.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request,$id)
    {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $user->setUsername($request->query->get('username'));
        $user->setName($request->query->get('name'));
        $user->setLastname($request->query->get('lastname'));
        $user->setEmail($request->query->get('email'));

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        $serializer = new Serializer([new ObjectNormalizer()]);

        if (null !== $event->getResponse()) {
            $formatted = $serializer->normalize(array("false"));

        }


        if (!empty($this->userManager)) {
            $this->userManager = $this->get('fos_user.user_manager');

            $this->userManager->updateUser($user);
        }

        if (null === $response = $event->getResponse()) {

            $formatted = $serializer->normalize(array($user));
        }else {
            $formatted = $serializer->normalize(array("false"));

        }



        return new JsonResponse($formatted);
    }
    public function editAvatarAction(Request $request,$id){
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $user->setImage($request->query->get('image'));


        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        $serializer = new Serializer([new ObjectNormalizer()]);

        if (null !== $event->getResponse()) {
            $formatted = $serializer->normalize(array("false"));

        }


        if (!empty($this->userManager)) {
            $this->userManager = $this->get('fos_user.user_manager');

            $this->userManager->updateUser($user);
        }

        if (null === $response = $event->getResponse()) {

            $formatted = $serializer->normalize(array($user));
        }else {
            $formatted = $serializer->normalize(array("false"));

        }



        return new JsonResponse($formatted);
    }
}
