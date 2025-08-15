# Configuration Production - Système de Persistance de Session

## ✅ **Configuration Actuelle**

Le système "Remember Me" est maintenant **ACTIVÉ** avec l'APP_SECRET configuré.

### **1. Système "Remember Me" Actif**
- Configuré dans `config/packages/security.yaml`
- Activé dans `FaceitCallbackController`
- Listener activé dans `config/services.yaml`
- APP_SECRET configuré : `ee7d6955f7afcbb3e563de7523cfe7d9ffaa9266092b432de73d618a8a746914`

### **2. Correction des Templates d'Erreur**
- Remplacé `homepage` par `app_home` dans les templates d'erreur

## 🚀 **Configuration Production Complète**

### **Variables d'Environnement Requises**

```bash
# REQUIS pour la production
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=your_super_secret_key_here_that_is_at_least_32_characters_long_for_production_use

# Base de données
DATABASE_URL="mysql://username:password@host:port/database?serverVersion=8.0.32&charset=utf8mb4"

# Faceit OAuth
FACEIT_CLIENT_ID=your_production_faceit_client_id
FACEIT_CLIENT_SECRET=your_production_faceit_client_secret
```

### **Génération d'APP_SECRET**

```bash
# Générer une clé secrète sécurisée
php -r "echo bin2hex(random_bytes(32));"
```

### **Activation du "Remember Me" en Production**

Une fois `APP_SECRET` configuré, décommenter dans :

1. **`config/packages/security.yaml`**
2. **`FaceitCallbackController`**
3. **`config/services.yaml`**

## 📋 **Étapes de Déploiement**

### **1. Configuration des Variables d'Environnement**
```bash
# Dans votre plateforme de déploiement (Scalingo, Heroku, etc.)
APP_SECRET=your_generated_secret_key
APP_ENV=prod
APP_DEBUG=0
```

### **2. Build des Assets**
```bash
# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Build des assets (si vous utilisez Webpack Encore)
npm run build
# ou
yarn build
```

### **3. Cache et Optimisations**
```bash
# Vider le cache
php bin/console cache:clear --env=prod

# Optimiser l'autoloader
composer dump-autoload --optimize --no-dev --classmap-authoritative
```

### **4. Permissions des Dossiers**
```bash
# Dossiers nécessitant des permissions d'écriture
chmod -R 777 var/
chmod -R 777 public/uploads/
```

## 🔍 **Vérification Post-Déploiement**

### **1. Test de Connexion**
- Vérifier que la page d'accueil se charge
- Tester la connexion Faceit
- Vérifier que les assets CSS/JS se chargent

### **2. Vérification des Logs**
- Surveiller les logs d'erreur
- Vérifier qu'il n'y a plus d'erreurs 500

### **3. Test de Session**
- Se connecter et vérifier que la session persiste
- Tester la déconnexion

## 🚨 **En Cas de Problème**

### **1. Vérifier les Variables d'Environnement**
```bash
# Vérifier que APP_SECRET est défini
echo $APP_SECRET
```

### **2. Vérifier les Logs**
```bash
# Logs Symfony
tail -f var/log/prod.log

# Logs de l'application
tail -f var/log/app.log
```

### **3. Mode Debug Temporaire**
```bash
# Activer temporairement le debug
APP_DEBUG=1
APP_ENV=dev
```

## 📝 **Notes Importantes**

- **Ne jamais commiter** `APP_SECRET` dans le code
- **Toujours utiliser** `APP_ENV=prod` en production
- **Désactiver** `APP_DEBUG` en production
- **Vérifier** que tous les assets sont buildés et accessibles
- **Tester** la connexion Faceit après déploiement

## ✅ **Système "Remember Me" Actif**

Le système de persistance de session est maintenant complètement configuré et fonctionnel :

- ✅ APP_SECRET configuré
- ✅ Configuration security.yaml activée
- ✅ Service FaceitCallbackController activé
- ✅ Listener SessionPersistenceListener activé
- ✅ Cache vidé et prêt à l'emploi
