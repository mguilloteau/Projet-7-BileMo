<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
			$exception = $event->getThrowable();
			$response = new JsonResponse();

			if ($exception instanceof HttpExceptionInterface) {
				$data = [
					"status" => $exception->getStatusCode(),
					"message" => "Error : " . $exception->getMessage()
				];
				$response->setStatusCode($exception->getStatusCode());
				$response->headers->replace($exception->getHeaders());
				$response->setData($data);
			}

			$event->setResponse($response);

    }

    public static function getSubscribedEvents(): array
		{
        return [
            "kernel.exception" => "onKernelException",
						"kernel.event"
        ];
    }
}
