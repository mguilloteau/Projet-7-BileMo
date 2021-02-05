<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
		 * @OA\Tag(name="Login")
     * @Route("/api/login", name="app_login", methods={"POST"})
     */
    public function login()
		{
    }
}
