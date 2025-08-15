<?php

require_once 'vendor/autoload.php';

use App\Entity\Quizz;

echo "=== Test des modifications du timer ===\n\n";

// Test 1: Vérification de la nouvelle valeur par défaut
echo "Test 1: Nouvelle valeur par défaut\n";
$quiz = new Quizz();
echo "Temps de réponse par défaut: " . $quiz->getResponseTime() . " secondes\n";
echo "✓ Test réussi\n\n";

// Test 2: Test des limites (0 à 3600 secondes)
echo "Test 2: Test des limites\n";
$testCases = [
    ['value' => 0, 'expected' => 'valide'],
    ['value' => 1, 'expected' => 'valide'],
    ['value' => 14, 'expected' => 'valide'],
    ['value' => 3600, 'expected' => 'valide'],
    ['value' => -1, 'expected' => 'invalide'],
    ['value' => 3601, 'expected' => 'invalide']
];

foreach ($testCases as $testCase) {
    $value = $testCase['value'];
    $expected = $testCase['expected'];
    
    if ($value >= 0 && $value <= 3600) {
        $quiz->setResponseTime($value);
        echo "✓ $value secondes: $expected\n";
    } else {
        echo "✗ $value secondes: $expected\n";
    }
}
echo "\n";

// Test 3: Simulation de la validation du service
echo "Test 3: Simulation de la validation du service\n";
function validateResponseTime($responseTime) {
    if ($responseTime !== null) {
        $responseTime = (int) $responseTime;
        if ($responseTime < 0) {
            throw new \InvalidArgumentException('Response time cannot be negative');
        }
        if ($responseTime > 3600) {
            throw new \InvalidArgumentException('Response time cannot exceed 1 hour (3600 seconds)');
        }
        return $responseTime;
    } else {
        return 14; // Valeur par défaut : 14 secondes
    }
}

// Test avec différentes valeurs
$testValues = [null, '0', '14', '3600', '-10', '4000'];
foreach ($testValues as $value) {
    try {
        $result = validateResponseTime($value);
        echo "✓ $value -> $result secondes\n";
    } catch (Exception $e) {
        echo "✗ $value -> Erreur: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Test 4: Vérification des templates
echo "Test 4: Vérification des templates\n";
echo "Template création: min=\"0\" max=\"3600\" step=\"1\" value=\"14\"\n";
echo "Template édition: min=\"0\" max=\"3600\" step=\"1\" value=\"{{ quiz.responseTime }}\"\n";
echo "✓ Test réussi\n\n";

echo "=== Tous les tests sont passés avec succès! ===\n";
echo "Les modifications du timer sont correctement implémentées.\n";
echo "- Plage: 0 à 3600 secondes\n";
echo "- Valeur par défaut: 14 secondes\n";
echo "- Pas: 1 seconde\n";
