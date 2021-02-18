<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
		 * @OA\Post(
		 *   path="/api/login/",
		 *   summary="Connection to the BileMo application",
		 *   @OA\RequestBody(
		 *       required=true,
		 *       @OA\MediaType(
		 *           mediaType="application/json",
		 *           @OA\Schema(
		 *               type="object",
		 *               @OA\Property(
		 *                   property="username",
		 *                   description="Name of your phone",
		 *                   type="string"
		 *               ),
		 *               @OA\Property(
		 *                   property="password",
		 *                   description="Price of your phone",
		 *                   type="string"
		 *               ),
		 *   					)
	   * 				)
     * 		)
		 * )
		 * @OA\Response(response="200", description="Obtaining the authentication token")
		 * @OA\Response(response="401", description="Invalid Credentials")
		 * @OA\Tag(name="Authentication")
     * @Route("/api/login/", name="app_login", methods={"POST"})
     */
    public function login()
		{
    }
}
