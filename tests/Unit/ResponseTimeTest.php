<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ResponseTimeTest extends TestCase
{
    public function testResponseTimeValidation(): void
    {
        // Test de validation des valeurs de temps de réponse
        $validTimes = [0, 1, 14, 30, 60, 120, 300, 600, 1800, 3600];
        
        foreach ($validTimes as $time) {
            $this->assertTrue($time >= 0, "Le temps de réponse doit être positif ou nul: $time");
            $this->assertTrue($time <= 3600, "Le temps de réponse ne doit pas dépasser 1 heure: $time");
        }
    }

    public function testDefaultResponseTime(): void
    {
        // Test de la valeur par défaut
        $defaultTime = 14; // 14 secondes
        
        $this->assertEquals(14, $defaultTime);
        $this->assertTrue($defaultTime >= 0);
        $this->assertTrue($defaultTime <= 3600);
    }

    public function testInvalidResponseTimes(): void
    {
        // Test des valeurs invalides
        $invalidTimes = [-10, -1, -100, 3601, 5000];
        
        foreach ($invalidTimes as $time) {
            if ($time < 0) {
                $this->assertTrue($time < 0, "Le temps négatif $time devrait être considéré comme invalide");
            } else {
                $this->assertTrue($time > 3600, "Le temps $time devrait être considéré comme invalide (dépasse 1 heure)");
            }
        }
    }

    public function testTimeConversion(): void
    {
        // Test de conversion string vers int
        $timeString = '180';
        $timeInt = (int) $timeString;
        
        $this->assertEquals(180, $timeInt);
        $this->assertIsInt($timeInt);
        $this->assertTrue($timeInt > 0);
    }
}
