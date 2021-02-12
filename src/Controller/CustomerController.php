<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Paginator\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/customers")
 */
class CustomerController extends AbstractController
{
	const LIMIT = 10;

	private $serializer;
	private $entityManager;
	private $paginator;

	/**
	 * PhoneController constructor.
	 * @param SerializerInterface $serializer
	 * @param EntityManagerInterface $entityManager
	 * @param Paginator $paginator
	 */
	public function __construct(
		SerializerInterface $serializer,
		EntityManagerInterface $entityManager,
		Paginator $paginator
	)
	{
		$this->serializer = $serializer;
		$this->entityManager = $entityManager;
		$this->paginator = $paginator;
	}

	/**
	 * @OA\Get(
	 *   path="/api/customers/{id}",
	 *   summary="Get an existing customer by his ID"
	 * )
	 * @OA\Response(response="200", description="Get an object of the customer", @Model(type=Customer::class,groups={"list_customers"}))
	 * @OA\Response(response="401",description="Token Error")
	 * @OA\Response(response="404", description="Error : App\\Entity\\Customer object not found by the @ParamConverterannotation")
	 * @OA\Tag(name="Customer")
	 * @Route ("/{id}", name="details_customer", methods={"GET"})
	 * @Security(name="Bearer")
	 * @param Customer $customer
	 * @return Response
	 */
	public function getThisCustomer(Customer $customer) :
	Response
	{
		$data = $this->serializer->serialize($customer, "json", SerializationContext::create()->setGroups(['list_customers']));

		return new Response($data, Response::HTTP_OK , [
			"Content-Type" => "application/json"
		]);
	}

	/**
	* @OA\Get(
	*   path="/api/customers/",
	*   summary="List the registered customers on page of 10",
	*   @OA\Parameter(name="page",in="query", description="Page to filter by", required=false)
	* )
	* @OA\Response(response="200",description="List all customers (10 per page)",
	*   		@OA\JsonContent(type="array",@OA\Items(ref=@Model(type=Customer::class, groups={"list_customers"})))
	* )
	* @OA\Response(response="401",description="Token Error")
  * @OA\Response(response="404",description="There is no data present on this page. Try Again")
  * @OA\Tag(name="Customer")
	* @Route("/", name="list_customer", methods={"GET"})
  * @Security(name="Bearer")
	* @param Request $request
	* @return Response
	*/
	public function getAllCustomers(Request $request) :
	Response
	{
		$page = $this->paginator->getPage($request->query->get("page"));

		$customers = $this->paginator->paginate(Customer::class, $page, 10,  'list_customers');

		return new Response($customers, Response::HTTP_OK ,[
			"Content-Type" => "application/json"
		]);
	}
}
