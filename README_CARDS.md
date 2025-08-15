# Composant Card Universel

Ce composant Twig permet d'afficher des cards cohérentes et personnalisables dans toute l'application avec un design sombre moderne.

## Caractéristiques

- **Design sombre** : Fond noir/gris avec accents orange (#FF7700)
- **Responsive** : S'adapte à tous les écrans
- **Animations** : Effets de survol et animations d'apparition
- **Flexible** : Supporte différents types de contenu et d'actions
- **Cohérent** : Même style visuel partout dans l'application

## Utilisation

### Inclusion de base

```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'quiz',
    card_title: 'Titre de la card',
    card_description: 'Description de la card'
} %}
```

### Paramètres disponibles

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `card_type` | string | Type de card (quiz, tag, user, etc.) | `'quiz'` |
| `card_icon` | string | Classe FontAwesome de l'icône | `'fas fa-question-circle'` |
| `card_title` | string | Titre principal de la card | `'Nom du Quiz'` |
| `card_subtitle` | string | Sous-titre (optionnel) | `'#123'` |
| `card_description` | string | Description détaillée | `'Description du quiz...'` |
| `card_tags` | array | Tableau d'objets avec propriété `name` | `[{name: 'Tag1'}, {name: 'Tag2'}]` |
| `card_status` | object | Statut avec `text` et `class` | `{text: 'Terminé', class: 'quiz-done'}` |
| `card_scores` | object | Scores avec `user_score`, `ranking`, `best_score` | `{user_score: 85, ranking: 3}` |
| `card_actions` | array | Actions (boutons/liens) | Voir section Actions |
| `onclick_action` | string | Action JavaScript au clic | `'selectQuiz(123)'` |

### Types de cards prédéfinis

#### Card de Quiz
```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'quiz',
    card_icon: 'fas fa-question-circle',
    card_title: quiz.name,
    card_description: quiz.description,
    card_tags: quiz.tags,
    card_status: {
        text: 'Terminé',
        class: 'quiz-done'
    },
    card_scores: {
        user_score: 85,
        ranking: 3,
        best_score: 95
    }
} %}
```

#### Card de Tag
```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'tag',
    card_icon: 'fas fa-tag',
    card_title: tag.name,
    card_subtitle: '#' ~ tag.id,
    card_actions: [
        {
            type: 'link',
            url: path('edit_tag', {'id': tag.id}),
            icon: 'fas fa-edit',
            text: 'Modifier',
            class: 'btn-edit'
        }
    ]
} %}
```

#### Card d'Utilisateur
```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'user',
    card_icon: 'fas fa-user',
    card_title: user.pseudo,
    card_subtitle: '#' ~ user.id,
    card_description: user.email ~ ' • ' ~ user.role,
    card_actions: [
        {
            type: 'link',
            url: path('edit_user', {'id': user.id}),
            icon: 'fas fa-edit',
            text: 'Modifier',
            class: 'btn-edit'
        }
    ]
} %}
```

### Actions disponibles

#### Lien simple
```twig
{
    type: 'link',
    url: path('route_name'),
    icon: 'fas fa-edit',
    text: 'Modifier',
    class: 'btn-edit',
    title: 'Modifier'
}
```

#### Bouton avec action JavaScript
```twig
{
    type: 'button',
    icon: 'fas fa-play',
    text: 'Commencer',
    class: 'btn-primary',
    title: 'Commencer le quiz',
    onclick: 'startQuiz(123)'
}
```

#### Formulaire (pour suppression)
```twig
{
    type: 'form',
    url: path('delete_item', {'id': item.id}),
    icon: 'fas fa-trash',
    text: 'Supprimer',
    class: 'btn-delete',
    title: 'Supprimer',
    token: csrf_token('delete' ~ item.id),
    onsubmit: "return confirm('Êtes-vous sûr ?');"
}
```

## Classes CSS personnalisées

### Boutons d'action
- `btn-edit` : Bouton de modification (jaune)
- `btn-delete` : Bouton de suppression (rouge)
- `btn-primary` : Bouton principal (orange)

### Statuts
- `quiz-done` : Quiz terminé (vert)
- `quiz-notdone` : Quiz non terminé (rouge)

## Responsive Design

Le composant s'adapte automatiquement aux différentes tailles d'écran :

- **Desktop** : Grille multi-colonnes
- **Tablet** : Grille adaptée
- **Mobile** : Colonne unique avec espacement optimisé

## Personnalisation

### Modifier les couleurs
Éditez le fichier `assets/css/universal-card.css` pour changer :
- Couleurs de fond
- Couleurs d'accent
- Couleurs des boutons
- Ombres et bordures

### Ajouter de nouveaux types
1. Créez une nouvelle classe CSS dans le fichier CSS
2. Utilisez `card_type: 'nouveau_type'` dans vos templates
3. Ajoutez des styles spécifiques si nécessaire

## Exemples d'utilisation dans l'application

### Sélection de quiz
```twig
{% include 'partials/_quiz_card.html.twig' with {
    quiz: quiz,
    userQuizzStatuses: userQuizzStatuses,
    userScores: userScores,
    userRankings: userRankings,
    bestScores: bestScores
} %}
```

### Administration des tags
```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'tag',
    card_icon: 'fas fa-tag',
    card_title: tag.name,
    card_subtitle: '#' ~ tag.id,
    card_actions: [
        {
            type: 'link',
            url: path('admin_tag_edit', {'id': tag.id}),
            icon: 'fas fa-edit',
            text: 'Modifier',
            class: 'btn-edit'
        },
        {
            type: 'form',
            url: path('admin_tag_delete', {'id': tag.id}),
            icon: 'fas fa-trash',
            text: 'Supprimer',
            class: 'btn-delete',
            token: csrf_token('delete' ~ tag.id),
            onsubmit: "return confirm('Êtes-vous sûr ?');"
        }
    ]
} %}
```

## Migration depuis l'ancien système

### Avant (ancien quiz-card)
```twig
<div class="quiz-card">
    <h3>{{ quiz.name }}</h3>
    <p>{{ quiz.description }}</p>
    <!-- ... -->
</div>
```

### Après (composant universel)
```twig
{% include 'partials/_universal_card.html.twig' with {
    card_type: 'quiz',
    card_title: quiz.name,
    card_description: quiz.description
} %}
```

## Support

Pour toute question ou problème avec le composant, consultez :
1. Ce fichier README
2. Le template de démonstration `templates/demo_cards.html.twig`
3. Les exemples d'utilisation dans les templates existants
