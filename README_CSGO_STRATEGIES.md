# CS:GO Strategies System

Ce système permet de créer, gérer et partager des stratégies Counter-Strike: Global Offensive pour différentes maps.

## 🎯 Fonctionnalités

### Pour les utilisateurs
- **Parcourir les stratégies** par map, côté (T/CT), difficulté
- **Rechercher** des stratégies par mots-clés
- **Créer** des stratégies personnalisées avec positions des joueurs
- **Partager** des stratégies publiques ou privées
- **Visualiser** les statistiques et stratégies récentes

### Pour les administrateurs
- **Gérer** les maps et leurs descriptions
- **Modérer** le contenu des stratégies
- **Suivre** les statistiques d'utilisation

## 🗺️ Maps supportées

- de_dust2 - La map iconique du désert
- de_mirage - Thème Moyen-Orient avec angles complexes
- de_inferno - Village italien avec corridors serrés
- de_cache - Moderne et industriel
- de_overpass - Multi-niveaux avec angles uniques
- de_nuke - Centrale nucléaire avec gameplay vertical
- de_train - Gare de triage avec longues lignes de vue
- de_ancient - Ruines anciennes en jungle
- de_vertigo - Gratte-ciel avec gameplay vertical
- de_anubis - Architecture égyptienne ancienne

## 🚀 Installation

### 1. Créer les tables de base de données
```bash
# Créer la migration
php bin/console make:migration

# Exécuter la migration
php bin/console doctrine:migrations:migrate
```

### 2. Initialiser les maps par défaut
```bash
php bin/console app:initialize-maps
```

### 3. Vérifier que tout fonctionne
```bash
# Vérifier les routes
php bin/console debug:router | grep strategy

# Tester le service
php bin/console debug:container StrategyService
```

## 📁 Structure des fichiers

```
src/
├── Entity/
│   ├── Map.php                    # Entité pour les maps CS:GO
│   ├── Strategy.php               # Entité pour les stratégies
│   └── StrategyPosition.php       # Entité pour les positions des joueurs
├── Repository/
│   ├── MapRepository.php          # Requêtes sur les maps
│   ├── StrategyRepository.php     # Requêtes sur les stratégies
│   └── StrategyPositionRepository.php # Requêtes sur les positions
├── Service/
│   └── StrategyService.php        # Logique métier des stratégies
├── Controller/
│   └── StrategyController.php     # Contrôleur des stratégies
└── Command/
    └── InitializeMapsCommand.php  # Commande d'initialisation

templates/strategy/
├── index.html.twig                # Liste des stratégies
├── show.html.twig                 # Affichage d'une stratégie
└── new.html.twig                  # Création d'une stratégie
```

## 🔧 Utilisation

### Routes disponibles

- `GET /strategies` - Liste des stratégies avec filtres
- `GET /strategies/map/{slug}` - Stratégies par map
- `GET /strategies/side/{side}` - Stratégies par côté (T/CT)
- `GET /strategies/difficulty/{difficulty}` - Stratégies par difficulté
- `GET /strategies/{id}` - Affichage d'une stratégie
- `GET /strategies/new` - Créer une nouvelle stratégie
- `GET /strategies/{id}/edit` - Éditer une stratégie
- `POST /strategies/{id}/delete` - Supprimer une stratégie
- `GET /strategies/maps` - Liste des maps avec compteurs

### Créer une stratégie

1. Accéder à `/strategies/new`
2. Remplir les informations de base (titre, map, côté, difficulté)
3. Décrire la stratégie et les étapes d'exécution
4. Ajouter les positions des joueurs avec coordonnées
5. Définir les rôles et instructions pour chaque position
6. Sauvegarder la stratégie

### Exemple de stratégie

**Titre:** A Site Rush
**Map:** de_dust2
**Côté:** Terrorist
**Difficulté:** Easy
**Description:** Rush rapide vers le site A avec 5 joueurs

**Positions:**
- Player 1: Entry fragger, position (100, 200), rôle Entry
- Player 2: Support, position (150, 180), rôle Support
- Player 3: Lurker, position (80, 220), rôle Lurker
- Player 4: AWP, position (120, 160), rôle AWP
- Player 5: IGL, position (110, 190), rôle IGL

## 🎨 Personnalisation

### Ajouter de nouvelles maps

1. Créer une nouvelle entrée dans la base de données
2. Ajouter l'image de la map dans `public/uploads/images/`
3. Mettre à jour la commande `InitializeMapsCommand` si nécessaire

### Modifier les rôles des joueurs

Éditer le template `new.html.twig` pour ajouter/modifier les options de rôles :
- Entry (Premier à entrer)
- Support (Soutien)
- Lurker (Flanc)
- AWP (Sniper)
- IGL (Leader)

### Personnaliser le style

Modifier les fichiers CSS dans les templates ou créer un fichier CSS séparé dans `public/assets/css/`.

## 🔒 Sécurité

- Seuls les utilisateurs connectés peuvent créer des stratégies
- Les utilisateurs ne peuvent modifier/supprimer que leurs propres stratégies
- Protection CSRF sur tous les formulaires
- Validation des données côté serveur

## 📊 Statistiques

Le système collecte automatiquement :
- Nombre total de stratégies
- Nombre de stratégies par map
- Répartition T/CT
- Répartition par difficulté
- Stratégies récentes

## 🚧 Développement futur

- **Système de notation** des stratégies
- **Commentaires** et discussions
- **Vidéos** d'exécution
- **Import/Export** de stratégies
- **API** pour applications tierces
- **Système de tags** avancé
- **Recherche géolocalisée** des positions

## 🤝 Contribution

Pour contribuer au développement :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Implémenter les tests unitaires
4. Soumettre une pull request

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

## 🆘 Support

Pour toute question ou problème :
- Créer une issue sur GitHub
- Consulter la documentation Symfony
- Vérifier les logs d'erreur dans `var/log/`
