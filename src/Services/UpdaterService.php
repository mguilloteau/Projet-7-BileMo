<?php

	namespace App\Services;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Component\HttpFoundation\Exception\JsonException;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	class UpdaterService {

		private $entityManager;

		public function __construct(EntityManagerInterface $entityManager) {
			$this->entityManager = $entityManager;
		}

		public function updateThisEntity(Request $request, object $entity) :void {

			$data = json_decode($request->getContent());

			try {
				foreach ($data as $key => $value) {
					if ($key && !empty($value)) {
						$name = ucfirst($key);
						$setter = 'set'.$name;
						if (method_exists($entity, $setter)) {
							$entity->$setter($value);
						}
					}
				}
			} catch(\Throwable $e) {
				throw new JsonException("One of your value submitted is incorrect. Please read the doc @ 'api/doc/' and try again ! ", Response::HTTP_BAD_REQUEST);
			}

			$this->entityManager->flush();
		}
	}