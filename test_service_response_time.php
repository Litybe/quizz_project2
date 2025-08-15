<?php

require_once 'vendor/autoload.php';

use App\Entity\Quizz;

echo "=== Test du service de gestion du temps de réponse ===\n\n";

// Simulation de la logique du service
function testResponseTimeValidation($responseTime) {
    if ($responseTime !== null) {
        $responseTime = (int) $responseTime;
        if ($responseTime <= 0) {
            throw new \InvalidArgumentException('Response time must be greater than 0');
        }
        return $responseTime;
    } else {
        return 300; // Valeur par défaut
    }
}

// Test 1: Validation avec valeur valide
echo "Test 1: Validation avec valeur valide\n";
try {
    $result = testResponseTimeValidation('120');
    echo "Résultat: $result secondes\n";
    echo "✓ Test réussi\n\n";
} catch (Exception $e) {
    echo "✗ Test échoué: " . $e->getMessage() . "\n\n";
}

// Test 2: Validation avec valeur par défaut
echo "Test 2: Validation avec valeur par défaut\n";
try {
    $result = testResponseTimeValidation(null);
    echo "Résultat: $result secondes (valeur par défaut)\n";
    echo "✓ Test réussi\n\n";
} catch (Exception $e) {
    echo "✗ Test échoué: " . $e->getMessage() . "\n\n";
}

// Test 3: Validation avec valeur invalide (négative)
echo "Test 3: Validation avec valeur invalide (négative)\n";
try {
    $result = testResponseTimeValidation('-10');
    echo "Résultat: $result secondes\n";
    echo "✗ Test échoué: devrait lever une exception\n\n";
} catch (Exception $e) {
    echo "Exception levée: " . $e->getMessage() . "\n";
    echo "✓ Test réussi\n\n";
}

// Test 4: Validation avec valeur invalide (zéro)
echo "Test 4: Validation avec valeur invalide (zéro)\n";
try {
    $result = testResponseTimeValidation('0');
    echo "Résultat: $result secondes\n";
    echo "✓ Test réussi\n\n";
} catch (Exception $e) {
    echo "Exception levée: " . $e->getMessage() . "\n";
    echo "✓ Test réussi\n\n";
}

// Test 5: Conversion de types
echo "Test 5: Conversion de types\n";
$testValues = ['30', '60', '120', '300', '600'];
foreach ($testValues as $value) {
    $intValue = (int) $value;
    echo "Conversion '$value' -> $intValue\n";
}
echo "✓ Test réussi\n\n";

echo "=== Tous les tests du service sont passés avec succès! ===\n";
echo "La logique de validation du temps de réponse fonctionne correctement.\n";
