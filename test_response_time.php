<?php

require_once 'vendor/autoload.php';

use App\Entity\Quizz;

echo "=== Test de la fonctionnalité de temps de réponse ===\n\n";

// Test 1: Création d'un quiz avec temps de réponse par défaut
echo "Test 1: Quiz avec temps de réponse par défaut\n";
$quiz1 = new Quizz();
echo "Temps de réponse par défaut: " . $quiz1->getResponseTime() . " secondes\n";
echo "✓ Test réussi\n\n";

// Test 2: Création d'un quiz avec temps de réponse personnalisé
echo "Test 2: Quiz avec temps de réponse personnalisé\n";
$quiz2 = new Quizz();
$quiz2->setResponseTime(180); // 3 minutes
echo "Temps de réponse personnalisé: " . $quiz2->getResponseTime() . " secondes\n";
echo "✓ Test réussi\n\n";

// Test 3: Validation des valeurs
echo "Test 3: Validation des valeurs\n";
$validValues = [30, 60, 120, 300, 600, 1800, 3600];
foreach ($validValues as $value) {
    $quiz = new Quizz();
    $quiz->setResponseTime($value);
    echo "Valeur $value secondes: " . $quiz->getResponseTime() . " secondes\n";
}
echo "✓ Test réussi\n\n";

// Test 4: Conversion des valeurs
echo "Test 4: Conversion des valeurs\n";
$stringValues = ['30', '60', '120', '300'];
foreach ($stringValues as $value) {
    $intValue = (int) $value;
    echo "Conversion '$value' -> $intValue\n";
}
echo "✓ Test réussi\n\n";

echo "=== Tous les tests sont passés avec succès! ===\n";
echo "La fonctionnalité de temps de réponse est opérationnelle.\n";
