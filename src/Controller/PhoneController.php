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
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;


/**
 * @Route("/api/phones")
 */
class PhoneController extends AbstractController
{
		const LIMIT = 10;

		private $serializer;
		private $entityManager;
		private $paginator;
		private $cache;

	/**
	 * PhoneController constructor.
	 * @param SerializerInterface $serializer
	 * @param EntityManagerInterface $entityManager
	 * @param Paginator $paginator
	 * @param AdapterInterface $cache
	 */
	public function __construct(
		SerializerInterface $serializer,
		EntityManagerInterface $entityManager,
		Paginator $paginator,
		AdapterInterface $cache
	)
		{
			$this->serializer = $serializer;
			$this->entityManager = $entityManager;
			$this->paginator = $paginator;
			$this->cache = $cache;
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

			return new Response($data, Response::HTTP_OK , ["Content-Type" => "application/json"]);
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
	 */
    public function getAllPhones(Request $request) :
		Response
    {
			$page = $this->paginator->getPage($request->query->get("page"));
			$data = $value = $this->cache->get('my_cache_key', function (ItemInterface $item) {
				$item->expiresAfter(3600);
				$item->set($this->entityManager->getRepository(Phone::class)->findAll());
				$this->cache->save($item);
				return $item->get();
			});

    	$phones = $this->paginator->paginate($data, $page, 10);

			return new Response($phones, Response::HTTP_OK , ["Content-Type" => "application/json"]);
    }
}
