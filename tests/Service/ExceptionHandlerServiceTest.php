<?php

namespace App\Tests\Service;

use App\Service\ExceptionHandlerService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionHandlerServiceTest extends TestCase
{
    private ExceptionHandlerService $exceptionHandlerService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->exceptionHandlerService = new ExceptionHandlerService($this->logger);
    }

    public function testHandleQuizNotFound(): void
    {
        // Arrange
        $quizId = 123;
        $this->logger->expects($this->once())->method('warning')
            ->with('Quiz not found', ['quizId' => $quizId]);

        // Act & Assert
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Le quiz n\'existe pas');
        
        $this->exceptionHandlerService->handleQuizNotFound($quizId);
    }

    public function testHandleValidationError(): void
    {
        // Arrange
        $message = 'Invalid data provided';
        $context = ['field' => 'email', 'value' => 'invalid-email'];
        
        $this->logger->expects($this->once())->method('error')
            ->with('Validation error: ' . $message, $context);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        
        $this->exceptionHandlerService->handleValidationError($message, $context);
    }

    public function testHandleValidationErrorWithoutContext(): void
    {
        // Arrange
        $message = 'Invalid data provided';
        
        $this->logger->expects($this->once())->method('error')
            ->with('Validation error: ' . $message, []);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        
        $this->exceptionHandlerService->handleValidationError($message);
    }

    public function testHandleDatabaseError(): void
    {
        // Arrange
        $originalException = new \Exception('Database connection failed');
        $context = ['operation' => 'insert', 'table' => 'users'];
        
        $this->logger->expects($this->once())->method('error')
            ->with('Database error: Database connection failed', [
                'operation' => 'insert',
                'table' => 'users',
                'exception' => $originalException
            ]);

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Une erreur est survenue lors de l\'opération.');
        
        $this->exceptionHandlerService->handleDatabaseError($originalException, $context);
    }

    public function testHandleDatabaseErrorWithoutContext(): void
    {
        // Arrange
        $originalException = new \Exception('Database connection failed');
        
        $this->logger->expects($this->once())->method('error')
            ->with('Database error: Database connection failed', [
                'exception' => $originalException
            ]);

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Une erreur est survenue lors de l\'opération.');
        
        $this->exceptionHandlerService->handleDatabaseError($originalException);
    }

    public function testHandleDatabaseErrorWithComplexException(): void
    {
        // Arrange
        $originalException = new \PDOException('SQLSTATE[23000]: Integrity constraint violation');
        $context = ['operation' => 'update', 'entity' => 'User', 'id' => 456];
        
        $this->logger->expects($this->once())->method('error')
            ->with('Database error: SQLSTATE[23000]: Integrity constraint violation', [
                'operation' => 'update',
                'entity' => 'User',
                'id' => 456,
                'exception' => $originalException
            ]);

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Une erreur est survenue lors de l\'opération.');
        
        $this->exceptionHandlerService->handleDatabaseError($originalException, $context);
    }
}
