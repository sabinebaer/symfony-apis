<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getException();

        // Catch exceptions from the validator.
        if ($exception instanceof ExceptionInterface) {
            $response = new JsonResponse([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);

            $event->setResponse($response);
        }
    }
}
