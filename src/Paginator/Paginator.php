<?php

	namespace App\Paginator;

	use Knp\Component\Pager\PaginatorInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use JMS\Serializer\SerializationContext;
	use Symfony\Component\Cache\Adapter\AdapterInterface;
	use Symfony\Component\Cache\CacheItem;
	use Symfony\Component\HttpFoundation\Exception\JsonException;
	use JMS\Serializer\SerializerInterface;

	class Paginator {

		private $entityManager;
		private $serializer;
		private $paginator;
		private $cache;

		public function __construct(
			EntityManagerInterface $entityManager,
			SerializerInterface $serializer,
			PaginatorInterface $paginator,
			AdapterInterface $cache
		)
		{
			$this->entityManager = $entityManager;
			$this->serializer = $serializer;
			$this->paginator = $paginator;
			$this->cache = $cache;
		}

		public function getPage($page): int
		{
			return (is_null($page) || $page < 1) ? 1 : $page;
		}

		public function paginate(array $data, int $page = null, int $limit = null, string $group = null):
		string
		{

			$pagedItems = $this->paginator->paginate($data, $page, (!is_null($limit)) ? $limit : 10 );

			if(empty($pagedItems->getItems())) {

				throw new JsonException("There is no data present on this page. Try Again");
			}

			(is_null($group)) ?
				$data = $this->serializer->serialize($pagedItems->getItems(), "json")
				:
				$data = $this->serializer->serialize($pagedItems->getItems(), "json", SerializationContext::create()->setGroups
				([$group]))
			;

			return $data;
		}
	}