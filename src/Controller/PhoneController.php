<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Paginator\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


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
	 * @Route ("/{id}", name="details_phone", methods={"GET"})
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
	 * @Route("/{page<\d+>?1}", name="list_phone", methods={"GET"})
	 * @param Request $request
	 * @return Response
	 */
    public function getAllPhones(Request $request) :
		Response
    {
    	$page = $request->query->get("page");

			if(is_null($page) || $page < 1) {
				$page = 1;
			}

			$phones = $this->paginator->paginate(Phone::class, $page, 10);

			return new Response($phones, Response::HTTP_OK ,[
				"Content-Type" => "application/json"
			]);
    }

	/**
	 * @Route ("/", name="add_phone", methods={"POST"})
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
				'message' => 'Le téléphone a bien été ajouté'
			];

			return new JsonResponse($data, JsonResponse::HTTP_CREATED);

		}

	/**
	 * @Route ("/{id}", name="delete_phone", methods={"DELETE"})
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
			'message' => 'Le téléphone a bien supprimé'
		];

		return new JsonResponse($data, JsonResponse::HTTP_NO_CONTENT , [
			"Content-Type" => "application/json"
		]);
	}
}
