<?php

namespace App\Controller;

use App\Entity\User;
use App\Paginator\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
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
  * @OA\Tag(name="Users")
	* @Route("/{page<\d+>?1}", name="list_user", methods={"GET"})
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
			'message' => 'L\'utilisateur a bien été ajouté'
		];

		return new JsonResponse($data, JsonResponse::HTTP_CREATED);
	}

	/**
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
			'message' => 'L\'utilisateur a bien supprimé'
		];

		return new JsonResponse($data, JsonResponse::HTTP_NO_CONTENT , [
			"Content-Type" => "application/json"
		]);
	}
}
