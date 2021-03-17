<?php

namespace App\Controller;

use App\Cache\CacheData;
use App\Entity\User;
use App\Paginator\Paginator;
use App\Services\UpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * @Route("/api/users")
 */
class UserController extends AbstractController
{
	const LIMIT = 10;

	private $serializer;
	private $entityManager;
	private $paginator;
	private $updateService;
	private $cache;

	/**
	 * PhoneController constructor.
	 * @param SerializerInterface $serializer
	 * @param EntityManagerInterface $entityManager
	 * @param Paginator $paginator
	 * @param UpdaterService $updateService
	 * @param CacheData $cache
	 */
	public function __construct(
		SerializerInterface $serializer,
		EntityManagerInterface $entityManager,
		Paginator $paginator,
		UpdaterService $updateService,
		CacheData $cache
	)
	{
		$this->serializer = $serializer;
		$this->entityManager = $entityManager;
		$this->paginator = $paginator;
		$this->updateService = $updateService;
		$this->cache = $cache;
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
		$this->denyAccessUnlessGranted("view", $user);

		$data = $this->serializer->serialize($user, "json", SerializationContext::create()->setGroups(["list_users"]));

		return new Response($data, Response::HTTP_OK , ["Content-Type" => "application/json"]);
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
	public function getAllUsers(Request $request): Response
	{
		$query = $this->entityManager->getRepository(User::class)->findUsersPerCustomers($this->getUser()->getUsername());

		$data = $this->cache->getDataCached("users".$this->getUser()->getUsername(), $query);

		$users = $this->paginator->paginate($data, $this->paginator->getPage($request->query->get("page")), 10,"list_users");

		return new Response($users, Response::HTTP_OK , ["Content-Type" => "application/json"]);
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
	* @OA\Response(response="500",description="The requested data is missing or incorrect. Please refer to the documentation @ /api/doc/")
  * @OA\Tag(name="Users")
	* @Route ("/", name="add_user", methods={"POST"})
  * @Security(name="Bearer")
	* @param Request $request
	* @return JsonResponse
	*/
	public function addUser(Request $request) : JsonResponse
	{
		try {
			$data = $this->serializer->deserialize($request->getContent(), User::class, "json");

			$data->setCustomer($this->getUser());

			$errors = $this->updateService->addObject($data);

			if (is_array($errors)) {
				return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
			}

			$this->cache->deleteCache("users".$this->getUser()->getUsername());

		} catch (\Throwable $exception) {
			throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "The requested data is missing or incorrect. Please refer to the documentation @ /api/doc/");
		}

		return new JsonResponse(["status" => Response::HTTP_CREATED, "message" => "User has been added to the database !"], Response::HTTP_CREATED);
	}

	/**
	 * @OA\Put(
	 *   path="/api/users/{id}",
	 *   summary="Update an existing user",
	 * 	 @OA\RequestBody(
	 *       required=false,
	 *       @OA\MediaType(
	 *           mediaType="application/json",
	 *           @OA\Schema(
	 *               type="object",
	 *   						 @OA\Property(
	 *                   property="username",
	 *                   description="Username of your user",
	 *                   type="string"
	 *               ),
	 *               @OA\Property(
	 *                   property="name",
	 *                   description="Name of your user",
	 *                   type="string"
	 *               ),
	 *               @OA\Property(
	 *                   property="surname",
	 *                   description="Surname of your user",
	 *                   type="integer"
	 *               ),
	 *   						@OA\Property(
	 *                   property="email",
	 *                   description="Email of your user",
	 *                   type="string"
	 *               ),
	 *           )
	 *       )
	 *    ),
	 *   @OA\Parameter(
	 *         description="ID of the user",
	 *         in="path",
	 *         name="id",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     )
	 * )
	 * @OA\Response(response="200",description="Confirmation of user update")
	 * @OA\Response(response="400",description="Error: Some data are incorrect or missing. Try Again / The requested data is missing or incorrect. Please refer to documentation @ /api/doc/")
	 * @OA\Response(response="401",description="Token Error")
	 * @OA\Response(response="500",description="The requested data is missing or incorrect. Please refer to the documentation @ /api/doc/")
	 * @OA\Tag(name="Users")
	 * @Route("/{id}", name="update_user", methods={"PUT"})
	 * @Security(name="Bearer")
	 * @param Request $request
	 * @param User $user
	 * @return JsonResponse
	 */
	public function updateUser(Request $request, User $user): JsonResponse
	{
		try {
			$this->denyAccessUnlessGranted("update", $user);

			$updated = $this->updateService->updateThisEntry($request, $user);

			if(is_array($updated)) {
				return new JsonResponse($updated, Response::HTTP_BAD_REQUEST);
			}

			$this->cache->deleteCache("users".$this->getUser()->getUsername());

		} catch (\Throwable $exception) {
			throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "The requested data is missing or incorrect. Please refer to the documentation @ /api/doc/");
		}

		return new JsonResponse(["status" => Response::HTTP_OK, "message" => "User : " . $user->getId() . " has been updated !"], Response::HTTP_OK);
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
		$this->denyAccessUnlessGranted("delete", $user);

		$this->entityManager->remove($user);
		$this->entityManager->flush();

		$this->cache->deleteCache("users".$this->getUser()->getUsername());

		return new JsonResponse(["status" => Response::HTTP_OK, "message" => "User has been removed !"],
			Response::HTTP_NO_CONTENT);
	}
}
