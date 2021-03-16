<?php

	namespace App\Validator;


	use JMS\Serializer\SerializerInterface;
	use Symfony\Component\HttpFoundation\Exception\JsonException;
	use Symfony\Component\HttpFoundation\JsonResponse;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	class Validator {

		private $validator;
		private $serializer;

		public function __construct(ValidatorInterface $validator, SerializerInterface $serializer)
		{
			$this->validator = $validator;
			$this->serializer = $serializer;
		}

		public function verifyThisData($data) {
			$errors = $this->validator->validate($data);


			if(count($errors) > 0) {

				$dataError = [
					"code" => Response::HTTP_BAD_REQUEST,
					"error" => 'Error: Some data are incorrect or missing. Try Again.',
				];
				$messages = [];
				foreach ($errors as $violation) {
					$messages[$violation->getPropertyPath()][] = $violation->getMessage();
				}
				$dataError["error_details"] = [
					$messages
				];

				return $dataError;
			}
		return true;
	}
}