<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionHandlerService
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handleQuizNotFound(int $quizId): void
    {
        $this->logger->warning('Quiz not found', ['quizId' => $quizId]);
        throw new NotFoundHttpException('Le quiz n\'existe pas');
    }

    public function handleValidationError(string $message, array $context = []): void
    {
        $this->logger->error('Validation error: ' . $message, $context);
        throw new \InvalidArgumentException($message);
    }

    public function handleDatabaseError(\Exception $e, array $context = []): void
    {
        $this->logger->error('Database error: ' . $e->getMessage(), array_merge($context, [
            'exception' => $e
        ]));
        throw new \RuntimeException('Une erreur est survenue lors de l\'opération.');
    }
}
