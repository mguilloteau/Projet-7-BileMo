<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Paginator\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Cache\CacheInterface;


/**
 * @Route("/api/phones")
 */
class PhoneController extends AbstractController
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
	 *   path="/api/phones/{id}",
	 *   summary="Get an existing phone by his ID",
	 *   @OA\Parameter(
	 *         description="ID of the phone",
	 *         in="path",
	 *         name="id",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     )
	 * )
	 * @OA\Response(response="200", description="Get an object of the phone", @Model(type=Phone::class))
	 * @OA\Response(response="400",description="Error: Some data are incorrect or missing. Try Again")
	 * @OA\Response(response="401",description="Token Error")
	 * @OA\Response(response="404",description="There is no data present on this page. Try Again")
	 * @OA\Tag(name="Phones")
	 * @Route ("/{id}", name="details_phone", methods={"GET"})
	 * @Security(name="Bearer")
	 * @param Phone $phone
	 * @return Response
	 */
		public function getThisPhone(Phone $phone) :
		Response
		{
			$data = $this->serializer->serialize($phone, "json");

			return new Response($data, Response::HTTP_OK , [
				"Content-Type" => "application/json"
			]);
		}

	/**
	 * @OA\Get(
	 *   path="/api/phones/",
	 *   summary="List the registered telephones on page of 10",
	 *   @OA\Parameter(
	 *         name="page",
	 *         in="query",
	 *         description="Page to filter by",
	 *         required=false
	 *     )
	 * )
	 * @OA\Response(
	 *      response="200",
	 *      description="List all phones (10 per page)",
	 *   		@OA\JsonContent(
	 *        type="array",
	 *        @OA\Items(ref=@Model(type=Phone::class))
	 *     )
	 * )
	 * @OA\Response(response="401",description="Token Error")
	 * @OA\Tag(name="Phones")
	 * @Route("/", name="list_phone", methods={"GET"})
	 * @Security(name="Bearer")
	 * @param Request $request
	 * @return Response
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
    public function getAllPhones(Request $request, Stopwatch $stopwatch) :
		Response
    {
    	$cache = new FilesystemAdapter();

    	$page = $request->query->get("page");

			if(is_null($page) || $page < 1) {
				$page = 1;
			}

    	$key = 'phones?page=' . $page;

    	$phones = $cache->get($key, function (ItemInterface $item) use ($page) {

    		$item->expiresAfter(10);

				return $this->paginator->paginate(Phone::class, $page, 10);

			});

			return new Response($phones, Response::HTTP_OK ,[
				"Content-Type" => "application/json"
			]);
    }

	/**
	 * @OA\Post(
	 *   path="/api/phones/",
	 *   summary="Create a new phone",
	 * 	 @OA\RequestBody(
	 *       required=true,
	 *       @OA\MediaType(
	 *           mediaType="application/json",
	 *           @OA\Schema(
	 *               type="object",
	 *               @OA\Property(
	 *                   property="name",
	 *                   description="Name of your phone",
	 *                   type="string"
	 *               ),
	 *               @OA\Property(
	 *                   property="price",
	 *                   description="Price of your phone",
	 *                   type="integer"
	 *               ),
	 *   						@OA\Property(
	 *                   property="color",
	 *                   description="Color of your phone",
	 *                   type="string"
	 *               ),
	 *   						@OA\Property(
	 *                   property="description",
	 *                   description="Description of your phone",
	 *                   type="string"
	 *               )
	 *           )
	 *       )
	 *   )
	 * )
	 * @OA\Response(response="201",description="Confirmation of phone creation")
	 * @OA\Response(response="400",description="Error: Some data are incorrect or missing. Try Again")
	 * @OA\Response(response="401",description="Token Error")
	 * @OA\Tag(name="Phones")
	 * @Route ("/", name="add_phone", methods={"POST"})
	 * @Security(name="Bearer")
	 * @param Request $request
	 * @return JsonResponse
	 */
		public function addPhone(Request $request) :
		JsonResponse
		{

			$data = $this->serializer->deserialize($request->getContent(), Phone::class, "json");

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
				'message' => 'Phone has been added !'
			];

			return new JsonResponse($data, JsonResponse::HTTP_CREATED);

		}

	/**
	 * @OA\Delete (
	 *   path="/api/phones/{id}",
	 *   summary="Remove an existing phone by his ID",
	 *   @OA\Parameter(
	 *         description="ID of the phone",
	 *         in="path",
	 *         name="id",
	 *         required=true,
	 *         @OA\Schema(
	 *           type="integer"
	 *         )
	 *     )
	 * )
	 * @OA\Response(response="204", description="Confirmation of phone removal")
	 * @OA\Response(response="404", description="Error : App\\Entity\\Phone object not found by the @ParamConverter annotation")
	 * @OA\Response(response="401",description="Token Error")
	 * @OA\Tag(name="Phones")
	 * @Route ("/{id}", name="delete_phone", methods={"DELETE"})
	 * @Security(name="Bearer")
	 * @param Phone $phone
	 * @return Response
	 */
	public function deleteThisPhone(Phone $phone) :
	Response
	{
		$this->entityManager->remove($phone);
		$this->entityManager->flush();

		$data = [
			'status' => JsonResponse::HTTP_OK,
			'message' => 'This phone has been removed !'
		];

		return new JsonResponse($data, JsonResponse::HTTP_NO_CONTENT , [
			"Content-Type" => "application/json"
		]);
	}
}
