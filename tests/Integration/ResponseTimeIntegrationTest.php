<?php

namespace App\Tests\Integration;

use App\Entity\Quizz;
use PHPUnit\Framework\TestCase;

class ResponseTimeIntegrationTest extends TestCase
{
    public function testQuizCreationWithResponseTime(): void
    {
        // Test de création d'un quiz avec temps de réponse personnalisé
        $quiz = new Quizz();
        $quiz->setName('Test Quiz');
        $quiz->setDescription('Test Description');
        $quiz->setTimeWeight(0.3);
        $quiz->setCorrectAnswerWeight(0.7);
        $quiz->setResponseTime(180); // 3 minutes
        
        // Assertions
        $this->assertEquals('Test Quiz', $quiz->getName());
        $this->assertEquals('Test Description', $quiz->getDescription());
        $this->assertEquals(0.3, $quiz->getTimeWeight());
        $this->assertEquals(0.7, $quiz->getCorrectAnswerWeight());
        $this->assertEquals(180, $quiz->getResponseTime());
    }

    public function testQuizCreationWithDefaultResponseTime(): void
    {
        // Test de création d'un quiz avec temps de réponse par défaut
        $quiz = new Quizz();
        $quiz->setName('Test Quiz Default');
        $quiz->setDescription('Test Description Default');
        
        // Assertions - le temps de réponse par défaut devrait être 14 secondes
        $this->assertEquals(14, $quiz->getResponseTime());
    }

    public function testResponseTimeValidation(): void
    {
        // Test de validation des temps de réponse
        $quiz = new Quizz();
        
        // Test avec des valeurs valides
        $validTimes = [0, 1, 14, 30, 60, 120, 300, 600, 1800, 3600];
        foreach ($validTimes as $time) {
            $quiz->setResponseTime($time);
            $this->assertEquals($time, $quiz->getResponseTime());
        }
    }

    public function testResponseTimeEdgeCases(): void
    {
        // Test des cas limites
        $quiz = new Quizz();
        
        // Temps minimum (0 seconde)
        $quiz->setResponseTime(0);
        $this->assertEquals(0, $quiz->getResponseTime());
        
        // Temps maximum (1 heure)
        $quiz->setResponseTime(3600);
        $this->assertEquals(3600, $quiz->getResponseTime());
        
        // Temps par défaut (14 secondes)
        $quiz->setResponseTime(14);
        $this->assertEquals(14, $quiz->getResponseTime());
    }
}
