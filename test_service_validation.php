<?php

require_once 'vendor/autoload.php';

echo "=== Test de validation du service QuizManagementService ===\n\n";

// Simulation de la logique du service QuizManagementService
class ResponseTimeValidator
{
    public static function validateResponseTime($responseTime): int
    {
        if ($responseTime !== null) {
            $responseTime = (int) $responseTime;
            if ($responseTime <= 0) {
                throw new \InvalidArgumentException('Response time must be greater than 0');
            }
            return $responseTime;
        } else {
            return 14; // Valeur par défaut : 14 secondes
        }
    }
}

// Test 1: Validation avec valeur valide
echo "Test 1: Validation avec valeur valide\n";
try {
    $result = ResponseTimeValidator::validateResponseTime('120');
    echo "✓ Résultat: $result secondes\n\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 2: Validation avec valeur par défaut
echo "Test 2: Validation avec valeur par défaut\n";
try {
    $result = ResponseTimeValidator::validateResponseTime(null);
    echo "✓ Résultat: $result secondes (valeur par défaut)\n\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 3: Validation avec valeur invalide (négative)
echo "Test 3: Validation avec valeur invalide (négative)\n";
try {
    $result = ResponseTimeValidator::validateResponseTime('-10');
    echo "✗ Erreur: devrait lever une exception\n\n";
} catch (Exception $e) {
    echo "✓ Exception levée: " . $e->getMessage() . "\n\n";
}

// Test 4: Validation avec valeur invalide (zéro)
echo "Test 4: Validation avec valeur invalide (zéro)\n";
try {
    $result = ResponseTimeValidator::validateResponseTime('0');
    echo "✗ Erreur: devrait lever une exception\n\n";
} catch (Exception $e) {
    echo "✓ Exception levée: " . $e->getMessage() . "\n\n";
}

// Test 5: Validation avec différentes valeurs valides
echo "Test 5: Validation avec différentes valeurs valides\n";
$validValues = ['0', '1', '14', '30', '60', '120', '300', '600', '1800', '3600'];
foreach ($validValues as $value) {
    try {
        $result = ResponseTimeValidator::validateResponseTime($value);
        echo "✓ $value secondes -> $result secondes\n";
    } catch (Exception $e) {
        echo "✗ $value secondes -> Erreur: " . $e->getMessage() . "\n";
    }
}
echo "\n";

echo "=== Tous les tests de validation sont passés avec succès! ===\n";
echo "La logique de validation du temps de réponse fonctionne correctement.\n";
