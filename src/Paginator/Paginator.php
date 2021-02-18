<?php

	namespace App\Paginator;

	use Knp\Component\Pager\PaginatorInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use JMS\Serializer\SerializationContext;
	use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
	use JMS\Serializer\SerializerInterface;

	class Paginator {

		private $entityManager;
		private $serializer;
		private $paginator;

		public function __construct(
			EntityManagerInterface $entityManager,
			SerializerInterface $serializer,
			PaginatorInterface $paginator)
		{
			$this->entityManager = $entityManager;
			$this->serializer = $serializer;
			$this->paginator = $paginator;
		}

		public function getPage($page): int
		{
			return (is_null($page) || $page < 1) ? 1 : $page;
		}

		public function paginate(array $data, int $page = null, int $limit = null,
			string
		$group = null): string
		{

			$items = $this->paginator->paginate($data, $page, (!is_null($limit)) ? $limit : 10 );

			if(empty($items->getItems())) {
				throw new NotFoundHttpException("There is no data present on this page. Try Again");
			}

			(is_null($group)) ?
				$data = $this->serializer->serialize($items->getItems(), "json")
				:
				$data = $this->serializer->serialize($items->getItems(), "json", SerializationContext::create()->setGroups([$group]))
			;

			return $data;
		}
	}