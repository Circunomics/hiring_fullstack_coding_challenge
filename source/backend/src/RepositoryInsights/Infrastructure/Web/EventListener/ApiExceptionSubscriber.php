<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Infrastructure\Web\EventListener;

use App\RepositoryInsights\Domain\Exception\ProviderRateLimited;
use App\RepositoryInsights\Domain\Exception\RemoteRepositoryNotFound;
use App\RepositoryInsights\Domain\Exception\TrackedRepositoryNotFound;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final readonly class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', 32]];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $requestPath = $event->getRequest()->getPathInfo();
        if ($requestPath !== '/api' && !str_starts_with($requestPath, '/api/')) {
            return;
        }

        $throwable = $event->getThrowable();
        [$status, $code, $message] = $this->classify($throwable);

        if ($status >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->logger->error('API request failed', [
                'exception' => $throwable,
                'path' => $requestPath,
            ]);
        }

        $event->setResponse(new JsonResponse([
            'error' => ['code' => $code, 'message' => $message],
        ], $status));
    }

    /** @return array{0: int, 1: string, 2: string} */
    private function classify(Throwable $throwable): array
    {
        return match (true) {
            $throwable instanceof TrackedRepositoryNotFound => [
                Response::HTTP_NOT_FOUND, 'tracked_repository_not_found', $throwable->getMessage(),
            ],
            $throwable instanceof RemoteRepositoryNotFound => [
                Response::HTTP_NOT_FOUND, 'remote_repository_not_found', $throwable->getMessage(),
            ],
            $throwable instanceof ProviderRateLimited => [
                Response::HTTP_TOO_MANY_REQUESTS, 'provider_rate_limited', $throwable->getMessage(),
            ],
            $throwable instanceof InvalidArgumentException,
            $throwable instanceof \ValueError => [
                Response::HTTP_BAD_REQUEST, 'bad_request', $throwable->getMessage(),
            ],
            $throwable instanceof \JsonException => [
                Response::HTTP_BAD_REQUEST, 'invalid_json', $throwable->getMessage(),
            ],
            $throwable instanceof HttpExceptionInterface && $throwable->getStatusCode() < 500 => [
                $throwable->getStatusCode(), 'bad_request', $throwable->getMessage(),
            ],
            default => [
                Response::HTTP_INTERNAL_SERVER_ERROR, 'internal_error', 'An unexpected error occurred.',
            ],
        };
    }
}
