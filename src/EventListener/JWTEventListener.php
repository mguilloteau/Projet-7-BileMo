<?php

	namespace App\EventListener;

	use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
	use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTFailureEventInterface;
	use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
	use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
	use Symfony\Component\HttpFoundation\JsonResponse;

	class JWTEventListener {

		public function onJWTError(JWTFailureEventInterface $failureEvent) {

			$response = $failureEvent->getResponse();

			$dataResponse = [];

			if($failureEvent instanceof JWTExpiredEvent) {
				$dataResponse = [
					"status"  => $response->getStatusCode(),
					"message" => "Your token has expired! Please re-authenticate"
				];
			}
			else if ($failureEvent instanceof JWTNotFoundEvent) {

				$dataResponse = [
					"status"  => $response->getStatusCode(),
					"message" => "No connection token detected. Please login to use this resource",
				];

			} else if($failureEvent instanceof JWTInvalidEvent){
				$dataResponse = [
					"status"  => $response->getStatusCode(),
					"message" => "Token not valid! Please enter it again or login",
				];
			}

			$response = new JsonResponse($dataResponse, $dataResponse["status"]);

			$failureEvent->setResponse($response);
		}

	}