<?php

namespace App\Controller;

use App\Entity\User;
use App\Paginator\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/users")
 */
class UserController extends AbstractController
{
	const LIMIT = 10;

	private $serializer;
	private $entityManager;
	private $validator;
	private $paginator;

	/**
	 * PhoneController constructor.
	 * @param SerializerInterface $serializer
	 * @param EntityManagerInterface $entityManager
	 * @param ValidatorInterface $validator
	 * @param Paginator $paginator
	 */
	public function __construct(
		SerializerInterface $serializer,
		EntityManagerInterface $entityManager,
		ValidatorInterface $validator,
		Paginator $paginator
	)
	{
		$this->serializer = $serializer;
		$this->entityManager = $entityManager;
		$this->validator = $validator;
		$this->paginator = $paginator;
	}

	/**
	* @OA\Get(
	*   path="/api/users/{id}",
	*   summary="Get an existing user by his ID"
	* )
	* @OA\Response(
	*      response="200",
	*      description="Get an object of the user",
	*   		@Model(type=User::class, groups={"list_users"})
	* )
	* @OA\Response(response="401",description="Token Error")
  * @OA\Tag(name="Users")
	* @Route("/{id}", name="details_user", methods={"GET"})
  * @Security(name="Bearer")
	* @param User $user
	* @return Response
	*/
	public function getThisUser(User $user): Response
	{
		$data = $this->serializer->serialize($user, "json", SerializationContext::create()->setGroups(['list_users']));

		return new Response($data, Response::HTTP_OK , [
			"Content-Type" => "application/json"
		]);
	}

	/**
	* @OA\Get(
	*   path="/api/users/",
	*   summary="List the registered users on page of 10",
	*   @OA\Parameter(
	*         name="page",
	*         in="query",
	*         description="Page to filter by",
	*         required=false
	*     )
	* )
	* @OA\Response(
	*      response="200",
	*      description="List all users (10 per page)",
	*   		@OA\JsonContent(
	*        type="array",
	*        @OA\Items(ref=@Model(type=User::class, groups={"list_users"}))
	*     )
	* )
  * @OA\Response(response="401",description="Token Error")
 	* @OA\Response(response="404",description="There is no data present on this page. Try Again")
  * @OA\Tag(name="Users")
	* @Route("/", name="list_user", methods={"GET"})
  * @Security(name="Bearer")
	* @param Request $request
	* @return Response
	*/
	public function getAllUsers(Request $request) :
	Response
	{
		$page = $page = $this->paginator->getPage($request->query->get("page"));

		$users = $this->paginator->paginate(User::class, $page, 10,'list_users');

		return new Response($users, Response::HTTP_OK ,[
			"Content-Type" => "application/json"
		]);
	}

	/**
	* @OA\Post(
	*   path="/api/users/",
	*   summary="Create a new user",
	 * 	@OA\RequestBody(
	 *       required=true,
	 *       @OA\MediaType(
	 *           mediaType="application/json",
	 *           @OA\Schema(
	 *               type="object",
	 *               @OA\Property(
	 *                   property="username",
	 *                   description="Username of your user",
	 *                   type="string"
	 *               ),
	 *               @OA\Property(
	 *                   property="name",
	 *                   description="Name of your user",
	 *                   type="string"
	 *               ),
	 *   						@OA\Property(
	 *                   property="surname",
	 *                   description="Surname of your user",
	 *                   type="string"
	 *               ),
	 *   						@OA\Property(
	 *                   property="email",
	 *                   description="Description of your user",
	 *                   type="string"
	 *               )
	 *           )
	 *       )
	 * 		)
	* )
	* @OA\Response(
	*      response="201",
	*      description="Confirmation of user creation",
	* )
	* @OA\Response(response="400",description="Error: Some data are incorrect or missing. Try Again.")
	* @OA\Response(response="401",description="Token Error")
  * @OA\Tag(name="Users")
	* @Route ("/", name="add_user", methods={"POST"})
  * @Security(name="Bearer")
	* @param Request $request
	* @return JsonResponse
	*/
	public function addUser(Request $request) : JsonResponse
	{
		$data = $this->serializer->deserialize($request->getContent(), User::class, "json");

		$errors = $this->validator->validate($data);

		if(count($errors) > 0) {
			$dataError = [
				"code" => 400,
				"error" => 'Error: Some data are incorrect or missing. Try Again.',
			];
			$messages = [];
			foreach ($errors as $violation) {
				$messages[$violation->getPropertyPath()][] = $violation->getMessage();
			}
			$dataError["error_details"] = [
				$messages
			];
			return new JsonResponse($dataError, 400);
		}

		$this->entityManager->persist($data);
		$this->entityManager->flush();

		$data = [
			'status' => JsonResponse::HTTP_CREATED,
			'message' => 'User has been added to the database !'
		];

		return new JsonResponse($data, JsonResponse::HTTP_CREATED);
	}

	/**
	 * @OA\Delete (
	 *   path="/api/users/{id}",
	 *   summary="Remove an existing user by his ID"
	 * )
	 * @OA\Response(
	 *      response="204",
	 *      description="Confirmation of user removal",
	 * )
	 * @OA\Response(response="401",description="Token Error")
	 * @OA\Response(response="404", description="Error : App\\Entity\\Customer object not found by the @ParamConverterannotation")
	 * @OA\Tag(name="Users")
	 * @Route ("/{id}", name="delete_user", methods={"DELETE"})
	 * @Security(name="Bearer")
	 * @param User $user
	 * @return Response
	 */
	public function deleteThisPhone(User $user) :
	Response
	{
		$this->entityManager->remove($user);
		$this->entityManager->flush();

		$data = [
			'status' => JsonResponse::HTTP_OK,
			'message' => 'User has been removed !'
		];

		return new JsonResponse($data, JsonResponse::HTTP_NO_CONTENT , [
			"Content-Type" => "application/json"
		]);
	}
}
