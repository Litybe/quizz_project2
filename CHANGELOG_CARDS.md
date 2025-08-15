# Changelog - Composant Card Universel

## Version 2.0 - Suppression du rectangle jaune et ajout d'indicateurs spécifiques

### Modifications apportées

#### 1. Suppression du rectangle jaune à gauche
- **Supprimé** : Le pseudo-élément `::before` qui créait une ligne orange en haut de la card
- **Supprimé** : L'animation de transformation de cette ligne au survol
- **Résultat** : Les cards n'ont plus de rectangle jaune/orange à gauche

#### 2. Suppression du carré orange (icône)
- **Supprimé** : Le carré orange avec l'icône dans le coin supérieur gauche
- **Méthode** : CSS `display: none` pour masquer complètement l'élément
- **Résultat** : Les cards ont un design encore plus épuré sans l'icône orange

#### 3. Ajout d'indicateurs spécifiques selon le type de card

##### Quiz Cards
- **Quiz terminé** : Bordure gauche verte (#28a745)
- **Quiz non terminé** : Bordure gauche rouge (#dc3545)
- **Classe CSS** : `.quiz-card.quiz-done` et `.quiz-card.quiz-notdone`

##### Tag Cards
- **Aucune bordure** : Pas d'indicateur visuel
- **Classe CSS** : `.tag-card` avec `border-left: none`

##### User/Profile Cards
- **Aucune bordure** : Pas d'indicateur visuel
- **Classe CSS** : `.user-card` avec `border-left: none`

#### 4. Modifications du composant Twig
- **Ajout** : Logique pour appliquer automatiquement la classe de statut aux quiz
- **Structure** : Les classes sont maintenant appliquées directement à la card principale
- **Exemple** : `<div class="universal-card quiz-card quiz-done">`

### Fichiers modifiés

1. **`assets/css/universal-card.css`**
   - Suppression des règles `.universal-card::before`
   - Suppression des règles `.universal-card:hover::before`
   - Ajout des règles spécifiques pour chaque type de card

2. **`templates/partials/_universal_card.html.twig`**
   - Ajout de la logique de classes conditionnelles
   - Application automatique des classes de statut pour les quiz

### Exemples d'utilisation

#### Quiz avec statut
```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'quiz',
    card_status: {
        text: 'Terminé',
        class: 'quiz-done'  // → Bordure gauche verte
    }
} %}
```

#### Quiz sans statut
```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'quiz'
    // → Pas de bordure colorée
} %}
```

#### Tag ou User
```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'tag'  // ou 'user'
    // → Pas de bordure colorée
} %}
```

### Avantages des modifications

1. **Design plus épuré** : Suppression du rectangle jaune qui pouvait être distrayant
2. **Indicateurs contextuels** : Les quiz ont maintenant des indicateurs visuels clairs
3. **Cohérence visuelle** : Les autres types de cards restent neutres
4. **Accessibilité** : Les couleurs vert/rouge sont plus sémantiques pour les statuts

### Compatibilité

- ✅ **Rétrocompatible** : Tous les templates existants continuent de fonctionner
- ✅ **CSS existant** : Les autres styles restent inchangés
- ✅ **Responsive** : Toutes les fonctionnalités responsive sont préservées
- ✅ **Animations** : Les animations de survol sont maintenues

### Test

Pour tester les modifications, utilisez le template de démonstration :
- `templates/demo_cards.html.twig`
- Vérifiez que les quiz ont bien des bordures colorées selon leur statut
- Vérifiez que les tags et users n'ont pas de bordures colorées
