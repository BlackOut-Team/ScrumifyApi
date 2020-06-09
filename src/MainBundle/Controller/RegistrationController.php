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
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Controller managing the registration.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class RegistrationController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $userManager;
    private $tokenStorage;

    public function __construct(EventDispatcherInterface $eventDispatcher, FactoryInterface $formFactory, UserManagerInterface $userManager, TokenStorageInterface $tokenStorage)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $user->setUsername($request->query->get('username'));
        $user->setName($request->query->get('name'));
        $user->setLastname($request->query->get('lastname'));
        $user->setEmail($request->query->get('email'));
        $user->setImage($request->query->get('image'));


        $user->setPlainPassword($request->query->get('password'));

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
                    /*$message = Swift_Message::newInstance()
                        ->setSubject('Activate your Scrumify account')
                        ->setFrom('scrumify.application@gmail.com', 'Scrumify Team')
                        ->setTo($user->getEmail())
                        ->setContentType("text/html")
                        ->setBody(
                            $this->renderView('@Main/Registration/email.html.twig',
                                array('user' => $user->getUsername() , 'token' => $user->getId())));

                    $this->get('mailer')->send($message);*/

                }else {
                    $formatted = $serializer->normalize(array("false"));

                }



        return new JsonResponse($formatted);

    }

    /**
     * Tell the user to check their email provider.
     */
    public function checkEmailAction(Request $request)
    {
        $email = $request->getSession()->get('fos_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->generateUrl('fos_user_registration_register'));
        }

        $request->getSession()->remove('fos_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
        }

        return $this->render('@FOSUser/Registration/check_email.html.twig', array(
            'user' => $user,
        ));
    }


    public function confirmAction($token)
    {
        $u = $this->getDoctrine()->getRepository(User::class)->find($token);


        return $this->render('@Main/Registration/email.html.twig',
            array('user' => $u->getUsername() , 'token' => $u->getId()));

      /*  $u->setEnabled(true);
        $serializer = new Serializer([new ObjectNormalizer()]);

        $formatted = $serializer->normalize(array("true"));
        return new JsonResponse($formatted);*/

    }

    /**
     * Tell the user his account is now confirmed.
     */
    public function confirmedAction(Request $request,$token)
    {
        $u = $this->getDoctrine()->getRepository(User::class)->find($token);
        $u->setEnabled(true);
        $this->userManager->updateUser($u);
         return $this->render('@Main/Registration/confirmed.html.twig', array(
            'user' => $u,'token' => $token
        ));

    }
    public function doneAction(Request $request,$token)
    {
        $u = $this->getDoctrine()->getRepository(User::class)->find($token);

        $userManager = $this->userManager;

        $user = $userManager->findUserByUsername($u->getUserName());
        $serializer = new Serializer([new ObjectNormalizer()]);

        $user->setEnabled(true);
        $this->userManager->updateUser($user);
        if($user->isEnabled() ) {

            $formatted = $serializer->normalize(array("true"));
        }
        else {
            $formatted = $serializer->normalize(array("false"));
        }
        return new JsonResponse($formatted);

    }
    /**
     * @return string|null
     */
    private function getTargetUrlFromSession(SessionInterface $session)
    {
        $key = sprintf('_security.%s.target_path', $this->tokenStorage->getToken()->getProviderKey());

        if ($session->has($key)) {
            return $session->get($key);
        }

        return null;
    }


}
