<?php

	namespace App\Services;
	use App\Validator\Validator;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Component\HttpFoundation\Exception\JsonException;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	class UpdaterService {

		private $entityManager;
		private $validator;

		public function __construct(EntityManagerInterface $entityManager, Validator $validator) {
			$this->entityManager = $entityManager;
			$this->validator = $validator;
		}

		public function addObject(object $data) {

			try {
				$errors = $this->validator->verifyThisData($data);

				if(is_array($errors)) {
					return $errors;
				}
			} catch(\Throwable $e) {
				throw new JsonException("One of your value submitted is incorrect. Please read the doc @ 'api/doc/' and try again ! ", Response::HTTP_BAD_REQUEST);
			}

			$this->entityManager->persist($data);
			$this->entityManager->flush();

			return true;
		}

		public function updateThisEntry(Request $request, object $entity) {

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

				$errors = $this->validator->verifyThisData($entity);

				if(is_array($errors)) {
					return $errors;
				}

			} catch(\Throwable $e) {
				throw new JsonException("One of your value submitted is incorrect. Please read the doc @ 'api/doc/' and try again ! ", Response::HTTP_BAD_REQUEST);
			}

			$this->entityManager->flush();

			return true;
		}
	}