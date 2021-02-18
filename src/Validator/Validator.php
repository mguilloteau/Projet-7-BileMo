<?php

	namespace App\Validator;


	use Symfony\Component\HttpFoundation\JsonResponse;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	class Validator {

		private $validator;

		public function __construct(ValidatorInterface $validator)
		{
			$this->validator = $validator;
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
				return new JsonResponse($dataError, Response::HTTP_BAD_REQUEST);
			}
		return true;
	}
}