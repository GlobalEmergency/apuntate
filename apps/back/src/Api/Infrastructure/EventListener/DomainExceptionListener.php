<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class DomainExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof \DomainException) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $message = $exception->getMessage();
        $statusCode = str_contains(strtolower($message), 'not found')
            ? Response::HTTP_NOT_FOUND
            : Response::HTTP_BAD_REQUEST;

        $event->setResponse(new JsonResponse(
            ['error' => $message],
            $statusCode,
        ));
    }
}
