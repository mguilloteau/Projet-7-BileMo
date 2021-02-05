<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Paginator\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
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
	 * @Route ("/{id}", name="details_customer", methods={"GET"})
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
	* @Route("/{page<\d+>?1}", name="list_customer", methods={"GET"})
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
