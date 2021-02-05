<?php

	namespace App\Paginator;

	use Doctrine\ORM\EntityManagerInterface;
	use JMS\Serializer\SerializationContext;
	use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
	use JMS\Serializer\SerializerInterface;

	class Paginator {

		private $entityManager;
		private $serializer;

		public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer) {
			$this->entityManager = $entityManager;
			$this->serializer = $serializer;
		}

		public function getPage($page): int
		{
			return (is_null($page) || $page < 1) ? 1 : $page;
		}

		public function paginate(string $data, int $page = null, int $limit = null, string $group = null): string
		{

			$items = $this->entityManager->getRepository($data)->findBy([], [], $limit, $limit * ($page - 1));

			if(empty($items)) {
				throw new NotFoundHttpException("Aucun article n'est disponible sur cette page");
			}

			(is_null($group)) ?
				$data = $this->serializer->serialize($items, 'json')
				:
				$data = $this->serializer->serialize($items, 'json', SerializationContext::create()->setGroups([$group]))
			;

			return $data;
		}
	}