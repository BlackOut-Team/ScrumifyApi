<?php

namespace ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize(array(true));
        return new JsonResponse($formatted);
    }
    public function LoginAction()
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize(array(true));
        return new JsonResponse($formatted);
    }
}
