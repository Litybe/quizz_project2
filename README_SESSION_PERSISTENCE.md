# Système de Persistance de Session - Symfony

## 🎯 **Objectif**

Permettre aux utilisateurs de rester connectés même après avoir fermé leur navigateur ou quitté le site, évitant ainsi de se reconnecter à chaque visite.

## 🔧 **Solutions Implémentées**

### **1. Extension de la Durée de Session**

#### **Configuration Framework (`config/packages/framework.yaml`)**
```yaml
framework:
    session:
        enabled: true
        cookie_lifetime: 2592000        # 30 jours
        gc_maxlifetime: 2592000        # 30 jours côté serveur
        cookie_secure: auto
        cookie_httponly: true
        cookie_samesite: lax
```

#### **Configuration Security (`config/packages/security.yaml`)**
```yaml
security:
    firewalls:
        main:
            remember_me:
                secret: '%env(APP_SECRET)%'
                lifetime: 2592000       # 30 jours
                always_remember_me: true
                remember_me_parameter: _remember_me
```

### **2. Service de Persistance de Session**

#### **`SessionPersistenceService`**
- **`enableRememberMe(User $user)`** : Active le "Remember Me" pour un utilisateur
- **`isRememberMeEnabled()`** : Vérifie si le "Remember Me" est activé
- **`extendSessionIfRememberMe()`** : Prolonge la session si nécessaire
- **`clearRememberMe()`** : Nettoie les données lors de la déconnexion

### **3. Event Listener Automatique**

#### **`SessionPersistenceListener`**
- Se déclenche à chaque requête
- Prolonge automatiquement la session si le "Remember Me" est activé
- Priorité élevée (10) pour s'exécuter tôt dans le cycle de requête

## 🚀 **Fonctionnement**

### **Connexion (FaceitCallbackController)**
1. L'utilisateur se connecte via Faceit
2. Le `SessionPersistenceService` active automatiquement le "Remember Me"
3. La session est configurée pour durer 30 jours

### **Visites Ultérieures**
1. L'utilisateur revient sur le site
2. Le `SessionPersistenceListener` détecte la session active
3. La session est automatiquement prolongée de 30 jours
4. L'utilisateur reste connecté sans intervention

### **Déconnexion**
1. L'utilisateur se déconnecte
2. Le `SessionPersistenceService` nettoie toutes les données
3. La session est complètement supprimée

## 📊 **Durées Configurées**

- **Cookie de session** : 30 jours (2,592,000 secondes)
- **Session côté serveur** : 30 jours
- **"Remember Me"** : 30 jours
- **Prolongation automatique** : À chaque visite

## 🔒 **Sécurité**

- **HTTPS** : `cookie_secure: auto` (sécurisé en production)
- **HttpOnly** : `cookie_httponly: true` (protection XSS)
- **SameSite** : `cookie_samesite: lax` (protection CSRF)
- **Secret** : Utilise `APP_SECRET` pour signer les tokens

## 🧪 **Test**

### **Vérifier la Persistance**
1. Connectez-vous au site
2. Fermez complètement le navigateur
3. Rouvrez le navigateur et allez sur le site
4. Vous devriez être automatiquement connecté

### **Vérifier la Prolongation**
1. Connectez-vous et notez la date
2. Revenez sur le site quelques jours plus tard
3. La session devrait être prolongée automatiquement

### **Vérifier la Déconnexion**
1. Déconnectez-vous
2. Fermez le navigateur
3. Rouvrez et allez sur le site
4. Vous devriez être déconnecté

## 🐛 **Dépannage**

### **Session qui expire trop vite**
- Vérifiez `cookie_lifetime` et `gc_maxlifetime`
- Vérifiez que le listener est bien configuré

### **"Remember Me" qui ne fonctionne pas**
- Vérifiez la configuration `remember_me` dans security.yaml
- Vérifiez que `APP_SECRET` est bien défini

### **Problèmes de cookies**
- Vérifiez `cookie_secure`, `cookie_httponly`, `cookie_samesite`
- Testez en mode développement et production

## 📝 **Notes Importantes**

- **Développement** : Les sessions peuvent se comporter différemment
- **Production** : Assurez-vous que `APP_SECRET` est suffisamment sécurisé
- **HTTPS** : En production, `cookie_secure` doit être `true`
- **Base de données** : Les sessions sont stockées par défaut en fichiers
- **Performance** : Le listener s'exécute à chaque requête (impact minimal)

## 🔄 **Évolutions Possibles**

- **Stockage en base** : Migrer vers `session.storage.factory.pdo`
- **Sessions multiples** : Permettre plusieurs sessions par utilisateur
- **Expiration conditionnelle** : Expiration basée sur l'activité
- **Notifications** : Alerter l'utilisateur avant expiration
