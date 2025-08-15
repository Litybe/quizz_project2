<?php

namespace App\Tests\Integration;

use App\Entity\Quizz;
use PHPUnit\Framework\TestCase;

class ResponseTimeTest extends TestCase
{
    public function testQuizEntityHasResponseTimeField(): void
    {
        // Arrange
        $quiz = new Quizz();
        
        // Act
        $quiz->setResponseTime(120);
        
        // Assert
        $this->assertEquals(120, $quiz->getResponseTime());
    }

    public function testQuizEntityHasDefaultResponseTime(): void
    {
        // Arrange & Act
        $quiz = new Quizz();
        
        // Assert
        $this->assertEquals(300, $quiz->getResponseTime()); // 5 minutes par défaut
    }

    public function testResponseTimeValidation(): void
    {
        // Arrange
        $quiz = new Quizz();
        
        // Act & Assert - Valeurs valides
        $quiz->setResponseTime(30); // 30 secondes
        $this->assertEquals(30, $quiz->getResponseTime());
        
        $quiz->setResponseTime(3600); // 1 heure
        $this->assertEquals(3600, $quiz->getResponseTime());
        
        $quiz->setResponseTime(180); // 3 minutes
        $this->assertEquals(180, $quiz->getResponseTime());
    }
}
