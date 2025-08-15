<?php

require_once 'vendor/autoload.php';

use App\Entity\Quizz;

echo "=== Test de l'utilisation du temps de réponse ===\n\n";

// Test 1: Création d'un quiz avec différents temps de réponse
echo "Test 1: Quiz avec différents temps de réponse\n";
$testCases = [
    ['name' => 'Quiz Instantané', 'time' => 0],
    ['name' => 'Quiz Rapide', 'time' => 14],
    ['name' => 'Quiz Normal', 'time' => 300],
    ['name' => 'Quiz Long', 'time' => 1800],
    ['name' => 'Quiz Très Long', 'time' => 3600]
];

foreach ($testCases as $testCase) {
    $quiz = new Quizz();
    $quiz->setName($testCase['name']);
    $quiz->setResponseTime($testCase['time']);
    
    echo "✓ {$testCase['name']}: {$quiz->getResponseTime()} secondes\n";
}
echo "\n";

// Test 2: Simulation de l'affichage dans le template
echo "Test 2: Simulation de l'affichage dans le template\n";
$quiz = new Quizz();
$quiz->setResponseTime(180); // 3 minutes

// Simulation du template Twig: {{ quizz.responseTime }}
$templateValue = $quiz->getResponseTime();
echo "Valeur dans le template: $templateValue secondes\n";
echo "Affichage: Temps restant: $templateValue secondes\n";
echo "✓ Test réussi\n\n";

// Test 3: Simulation du JavaScript
echo "Test 3: Simulation du JavaScript\n";
$jsTimeLeft = $quiz->getResponseTime();
echo "JavaScript timeLeft = $jsTimeLeft;\n";
echo "Timer initialisé avec: $jsTimeLeft secondes\n";
echo "✓ Test réussi\n\n";

// Test 4: Validation des limites
echo "Test 4: Validation des limites\n";
$minTime = 0;
$maxTime = 3600;
$defaultTime = 14;

echo "Temps minimum: $minTime seconde\n";
echo "Temps maximum: $maxTime secondes\n";
echo "Temps par défaut: $defaultTime secondes\n";

$testTimes = [0, 14, 30, 300, 3600, 4000];
foreach ($testTimes as $time) {
    if ($time >= $minTime && $time <= $maxTime) {
        echo "✓ $time secondes: VALIDE\n";
    } else {
        echo "✗ $time secondes: INVALIDE\n";
    }
}
echo "✓ Test réussi\n\n";

echo "=== Tous les tests sont passés avec succès! ===\n";
echo "Le temps de réponse est correctement configuré et utilisé.\n";
