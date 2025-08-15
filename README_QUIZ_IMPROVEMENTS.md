# Améliorations des Pages de Création et Modification de Quiz

## Vue d'ensemble

Ce document décrit les améliorations apportées aux pages de création et modification de quiz pour améliorer l'expérience utilisateur et la fonctionnalité.

## Améliorations Implémentées

### 1. Page de Création de Quiz (`create-quizz.html.twig`)

#### Valeurs par défaut pour les poids
- **Poids du Temps de Réponse** : Valeur par défaut `0.2`
- **Poids des Bonnes Réponses** : Valeur par défaut `0.8`
- Ces valeurs sont maintenant pré-remplies dans les champs de saisie

#### Aperçu des images de questions
- **Fonctionnalité** : Affichage en temps réel d'un aperçu de l'image sélectionnée
- **Taille** : Aperçu limité à 150x150 pixels maximum
- **Format** : Accepte uniquement les fichiers image (`accept="image/*"`)
- **Style** : Aperçu encadré avec un fond gris clair et des ombres

#### Améliorations JavaScript
- **Fonction `handleImagePreview()`** : Gère l'affichage de l'aperçu d'image
- **Écouteurs d'événements** : Ajoutés automatiquement pour toutes les questions
- **Compatibilité** : Fonctionne pour les questions existantes et nouvelles

### 2. Page de Modification de Quiz (`edit.html.twig`)

#### Affichage des valeurs existantes
- **Poids** : Les valeurs stockées en base de données sont affichées dans les champs
- **Tags** : Les tags déjà associés au quiz sont pré-cochés
- **Images** : Les images existantes sont affichées en aperçu

#### Aperçu des images existantes et nouvelles
- **Images existantes** : Affichées automatiquement depuis le dossier `uploads/images/`
- **Nouvelles images** : Aperçu en temps réel lors de la sélection
- **Gestion des cas** : Gère les questions avec et sans images

#### Améliorations JavaScript
- **Écouteurs d'événements** : Ajoutés pour toutes les questions existantes
- **Nouvelles questions** : Gestion automatique des aperçus pour les questions ajoutées
- **Validation** : Maintien de la validation existante

### 3. Service QuizManagementService

#### Gestion des valeurs par défaut
```php
// Valeurs par défaut pour les poids si non fournies
$timeWeight = $request->request->get('timeWeight');
$correctAnswerWeight = $request->request->get('correctAnswerWeight');

$quiz->setTimeWeight($timeWeight !== null ? (float) $timeWeight : 0.2);
$quiz->setCorrectAnswerWeight($correctAnswerWeight !== null ? (float) $correctAnswerWeight : 0.8);
```

#### Gestion des images
- **Upload** : Gestion correcte des fichiers uploadés
- **Stockage** : Sauvegarde dans le dossier `uploads/images/`
- **Mise à jour** : Remplacement des anciennes images lors de la modification

### 4. Améliorations CSS

#### Styles pour les aperçus d'images
```css
.image-preview {
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}
.image-preview img {
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
```

#### Styles pour les questions
```css
.question {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    background-color: #fafafa;
}
```

## Fonctionnalités Techniques

### Gestion des Images
1. **Sélection de fichier** : Input de type `file` avec restriction aux images
2. **Aperçu en temps réel** : Utilisation de `FileReader` pour l'aperçu
3. **Stockage** : Sauvegarde avec nom unique généré par `uniqid()`
4. **Affichage** : Utilisation de `asset()` pour les images existantes

### Validation
- **Côté client** : Validation JavaScript pour les questions textuelles
- **Côté serveur** : Validation des poids et des données requises
- **Gestion d'erreurs** : Messages d'erreur appropriés

### Performance
- **Aperçu optimisé** : Taille limitée pour les aperçus
- **Chargement différé** : Images chargées uniquement si nécessaires
- **Gestion mémoire** : Nettoyage des aperçus lors du changement de fichier

## Utilisation

### Création d'un nouveau quiz
1. Remplir le nom et la description
2. Les poids sont pré-remplis (0.2 et 0.8)
3. Sélectionner les tags souhaités
4. Ajouter des questions avec ou sans images
5. Voir l'aperçu des images en temps réel
6. Sauvegarder le quiz

### Modification d'un quiz existant
1. Les valeurs existantes sont pré-remplies
2. Les tags associés sont pré-cochés
3. Les images existantes sont affichées
4. Possibilité de modifier ou ajouter de nouvelles images
5. Aperçu en temps réel des nouvelles images
6. Sauvegarder les modifications

## Compatibilité

### Navigateurs supportés
- **Chrome** : ✅ Aperçu d'images, FileReader
- **Firefox** : ✅ Aperçu d'images, FileReader
- **Safari** : ✅ Aperçu d'images, FileReader
- **Edge** : ✅ Aperçu d'images, FileReader

### Formats d'images supportés
- **JPEG** : ✅
- **PNG** : ✅
- **GIF** : ✅
- **WebP** : ✅ (selon le navigateur)

## Maintenance

### Ajout de nouvelles fonctionnalités
1. Modifier les templates HTML
2. Ajouter les styles CSS correspondants
3. Mettre à jour le JavaScript si nécessaire
4. Tester sur différents navigateurs

### Débogage
- **Console JavaScript** : Vérifier les erreurs de FileReader
- **Inspecteur réseau** : Vérifier les uploads d'images
- **Base de données** : Vérifier les chemins d'images stockés

## Conclusion

Ces améliorations offrent une meilleure expérience utilisateur en :
- Simplifiant la création de quiz avec des valeurs par défaut
- Permettant un aperçu visuel des images avant sauvegarde
- Conservant les données existantes lors de la modification
- Améliorant l'interface utilisateur avec des styles modernes
