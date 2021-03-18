<?php

	namespace App\Paginator;

	use Knp\Component\Pager\PaginatorInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use JMS\Serializer\SerializationContext;
	use Symfony\Component\Cache\Adapter\AdapterInterface;
	use Symfony\Component\Cache\CacheItem;
	use Symfony\Component\HttpFoundation\Exception\JsonException;
	use JMS\Serializer\SerializerInterface;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpKernel\Exception\HttpException;

	class Paginator {

		private $entityManager;
		private $serializer;
		private $paginator;

		public function __construct(
			EntityManagerInterface $entityManager,
			SerializerInterface $serializer,
			PaginatorInterface $paginator
		)
		{
			$this->entityManager = $entityManager;
			$this->serializer = $serializer;
			$this->paginator = $paginator;
		}

		public function getPage($page): int
		{
			return (is_null($page) || $page < 1) ? 1 : $page;
		}

		public function paginate(array $data, int $page = null, int $limit = null, string $group = null):
		string
		{
			$items = (!is_null($limit)) ? $this->paginator->paginate($data, $page, $limit )->getItems() : $data;


			if(empty($items)) {

				throw new HttpException(Response::HTTP_NOT_FOUND, "There is no data in this page. Try again !");
			}

			(is_null($group)) ?
				$data = $this->serializer->serialize($items, "json")
				:
				$data = $this->serializer->serialize($items, "json", SerializationContext::create()->setGroups
				([$group]))
			;

			return $data;
		}
	}