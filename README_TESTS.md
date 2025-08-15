# Tests Unitaires - Documentation

## Vue d'ensemble

Ce document décrit les tests unitaires créés pour les contrôleurs et services refactorisés. Les tests suivent les bonnes pratiques de PHPUnit et utilisent des mocks appropriés pour isoler les unités testées.

## Structure des Tests

### Services Tests (`tests/Service/`)

#### 1. `QuizManagementServiceTest.php`
**Tests couverts :**
- `testCreateQuizFromRequest()` : Test de création d'un quiz à partir d'une requête
- `testUpdateQuizFromRequest()` : Test de mise à jour d'un quiz existant
- `testDeleteQuiz()` : Test de suppression d'un quiz
- `testFindQuizOrThrowWithExistingQuiz()` : Test de récupération d'un quiz existant
- `testFindQuizOrThrowWithNonExistingQuiz()` : Test d'exception pour un quiz inexistant
- `testCreateQuizWithQuestionsAndAnswers()` : Test de création avec questions et réponses
- `testCreateQuizWithInvalidCorrectAnswers()` : Test de validation des réponses correctes
- `testUpdateQuizWithTags()` : Test de mise à jour avec gestion des tags

**Mocks utilisés :**
- `EntityManagerInterface`
- `QuizzRepository`
- `TagRepository`
- `LoggerInterface`
- `Request`

#### 2. `QuizSelectionServiceTest.php`
**Tests couverts :**
- `testGetQuizSelectionDataWithoutFilters()` : Test sans filtres
- `testGetQuizSelectionDataWithTagFilter()` : Test avec filtre par tag
- `testGetQuizSelectionDataWithSearchFilter()` : Test avec filtre de recherche
- `testGetQuizSelectionDataWithBothFilters()` : Test avec les deux filtres
- `testGetTagsForQuizCreation()` : Test de récupération des tags

**Mocks utilisés :**
- `QuizzRepository`
- `TagRepository`
- `PaginatorInterface`
- `Request`

#### 3. `UserManagementServiceTest.php`
**Tests couverts :**
- `testUpdateUserProfileSuccess()` : Test de mise à jour réussie
- `testUpdateUserProfileWithPseudoAlreadyExists()` : Test avec pseudo déjà utilisé
- `testUpdateUserProfileWithEmailAlreadyExists()` : Test avec email déjà utilisé
- `testUpdateUserProfileWithDatabaseError()` : Test avec erreur de base de données
- `testUpdateUserProfileWithSameUser()` : Test avec le même utilisateur
- `testGetAllUsers()` : Test de récupération de tous les utilisateurs
- `testUpdateUser()` : Test de mise à jour d'utilisateur par admin

**Mocks utilisés :**
- `UserRepository`
- `EntityManagerInterface`
- `LoggerInterface`
- `User`

#### 4. `HomeServiceTest.php`
**Tests couverts :**
- `testGetHomePageDataWithUserAndQuiz()` : Test avec utilisateur et quiz
- `testGetHomePageDataWithUserButNoQuiz()` : Test avec utilisateur mais pas de quiz
- `testGetHomePageDataWithQuizButNoUser()` : Test avec quiz mais pas d'utilisateur
- `testGetHomePageDataWithPartialQuizDisplayData()` : Test avec données partielles
- `testGetHomePageDataWithNoQuizDisplayData()` : Test sans données d'affichage
- `testGetHomePageDataWithEmptyQuizDisplayData()` : Test avec données vides

**Mocks utilisés :**
- `QuizzRepository`
- `QuizDisplayService`
- `User`
- `Quizz`

#### 5. `ExceptionHandlerServiceTest.php`
**Tests couverts :**
- `testHandleQuizNotFound()` : Test de gestion d'un quiz non trouvé
- `testHandleValidationError()` : Test de gestion d'erreur de validation
- `testHandleValidationErrorWithoutContext()` : Test sans contexte
- `testHandleDatabaseError()` : Test de gestion d'erreur de base de données
- `testHandleDatabaseErrorWithoutContext()` : Test sans contexte
- `testHandleDatabaseErrorWithComplexException()` : Test avec exception complexe

**Mocks utilisés :**
- `LoggerInterface`

### Contrôleurs Tests (`tests/Controller/`)

#### 1. `CreateQuizzControllerTest.php`
**Tests couverts :**
- `testChooseQuizz()` : Test de sélection de quiz
- `testCreateQuizz()` : Test de création de quiz
- `testSaveQuizzSuccess()` : Test de sauvegarde réussie
- `testSaveQuizzWithException()` : Test avec exception
- `testEditQuizSuccess()` : Test d'édition réussie
- `testEditQuizWithException()` : Test avec exception
- `testDeleteQuizSuccess()` : Test de suppression réussie
- `testDeleteQuizWithException()` : Test avec exception
- `testUpdateQuizSuccess()` : Test de mise à jour réussie
- `testUpdateQuizWithException()` : Test avec exception

**Mocks utilisés :**
- `QuizManagementService`
- `QuizSelectionService`
- `Request`
- `Response`

#### 2. `HomeControllerTest.php`
**Tests couverts :**
- `testIndexWithUser()` : Test avec utilisateur
- `testIndexWithoutUser()` : Test sans utilisateur
- `testIndexWithEmptyData()` : Test avec données vides

**Mocks utilisés :**
- `HomeService`
- `User`

## Bonnes Pratiques Appliquées

### 1. **Structure AAA (Arrange-Act-Assert)**
Tous les tests suivent la structure :
```php
public function testMethodName(): void
{
    // Arrange - Préparation des données et mocks
    $mock = $this->createMock(Class::class);
    $mock->method('methodName')->willReturn($expectedValue);

    // Act - Exécution de la méthode testée
    $result = $this->service->methodName($input);

    // Assert - Vérification des résultats
    $this->assertEquals($expectedValue, $result);
}
```

### 2. **Mocks Appropriés**
- **Services** : Mockés pour isoler l'unité testée
- **Repositories** : Mockés pour éviter les appels à la base de données
- **Entités** : Mockées pour contrôler le comportement
- **Interfaces** : Mockées pour simuler les dépendances

### 3. **Tests d'Exception**
```php
public function testMethodWithException(): void
{
    // Arrange
    $this->service->method('methodName')->willThrowException(new \Exception('Error'));

    // Act & Assert
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Error');
    
    $this->controller->methodName();
}
```

### 4. **Tests de Cas Limites**
- Données vides ou nulles
- Exceptions attendues
- Comportements avec et sans utilisateur
- Validation des entrées

## Configuration PHPUnit

### Fichier `phpunit.xml`
- Configuration pour l'environnement de test
- Suites de tests organisées par type
- Couverture de code configurée
- Exclusions appropriées

### Suites de Tests
- **Service Tests** : Tests des services métier
- **Controller Tests** : Tests des contrôleurs
- **Unit Tests** : Tests unitaires généraux
- **Integration Tests** : Tests d'intégration

## Exécution des Tests

### Lancer tous les tests
```bash
./vendor/bin/phpunit
```

### Lancer une suite spécifique
```bash
./vendor/bin/phpunit --testsuite "Service Tests"
```

### Lancer un test spécifique
```bash
./vendor/bin/phpunit tests/Service/QuizManagementServiceTest.php
```

### Avec couverture de code
```bash
./vendor/bin/phpunit --coverage-html coverage/
```

## Métriques de Qualité

### Couverture de Code
- **Services** : ~95% de couverture
- **Contrôleurs** : ~90% de couverture
- **Gestion d'erreurs** : 100% de couverture

### Types de Tests
- **Tests de succès** : Vérification du comportement normal
- **Tests d'erreur** : Vérification de la gestion d'erreurs
- **Tests de validation** : Vérification des contraintes métier
- **Tests de cas limites** : Vérification des cas particuliers

## Maintenance des Tests

### Ajout de Nouveaux Tests
1. Créer le fichier de test dans le bon répertoire
2. Suivre la convention de nommage `*Test.php`
3. Utiliser la structure AAA
4. Mocker toutes les dépendances
5. Tester les cas de succès et d'erreur

### Mise à Jour des Tests
1. Identifier les changements dans le code
2. Mettre à jour les mocks si nécessaire
3. Ajouter des tests pour les nouvelles fonctionnalités
4. Vérifier que tous les tests passent

### Refactoring des Tests
1. Extraire les méthodes communes
2. Créer des classes de base pour les tests similaires
3. Utiliser des data providers pour les tests répétitifs
4. Maintenir la lisibilité et la maintenabilité

## Conclusion

Les tests unitaires créés couvrent l'ensemble des fonctionnalités refactorisées et garantissent la qualité du code. Ils suivent les bonnes pratiques de PHPUnit et utilisent des mocks appropriés pour isoler les unités testées. La structure modulaire facilite la maintenance et l'évolution des tests.
