<?php

	namespace App\Services;
	use App\Validator\Validator;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Component\HttpFoundation\Exception\JsonException;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpKernel\Exception\HttpException;

	class UpdaterService {

		private $entityManager;
		private $validator;

		public function __construct(EntityManagerInterface $entityManager, Validator $validator) {
			$this->entityManager = $entityManager;
			$this->validator = $validator;
		}

		public function addObject(object $data) {

			$errors = $this->validator->verifyThisData($data);

			if(is_array($errors)) {
				return $errors;
			}

			$this->entityManager->persist($data);
			$this->entityManager->flush();

			return true;
		}

		public function updateThisEntry(Request $request, object $entity) {
			$data = json_decode($request->getContent());

			$entryUpdated = 0;

			foreach ($data as $key => $value) {
				if ($key && !empty($value)) {
					$name = ucfirst($key);
					$setter = 'set'.$name;
					if (method_exists($entity, $setter)) {
						$entity->$setter($value);
						$entryUpdated ++;
					}
				}
			}

			if($entryUpdated === 0) {
				throw new HttpException(Response::HTTP_BAD_REQUEST, "No data transmitted. Please refer to the documentation @ /api/doc");
			}

			$errors = $this->validator->verifyThisData($entity);

			if(is_array($errors)) {
				return $errors;
			}

			$this->entityManager->flush();

			return true;
		}
	}