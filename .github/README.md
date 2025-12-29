# 🚀 GitHub Actions - Résumé de la Configuration

## ✅ Fichiers créés

### Workflows GitHub Actions
```
.github/workflows/
├── ci.yml              # Tests automatiques (PHPUnit + Quality)
├── deploy.yml          # Déploiement SSH/rsync classique
└── deploy-docker.yml   # Déploiement via Docker Hub
```

### Documentation
```
docs/
├── DEPLOYMENT.md           # Guide complet de déploiement
└── QUICK_START_DEPLOY.md  # Guide rapide 5 minutes
```

### Scripts
```
scripts/
└── health-check.sh        # Vérification santé de l'application
```

### Configuration
```
.env.production.example    # Template configuration production
.dockerignore             # Optimisation build Docker
```

## 🎯 Ce que ça fait

### 1. CI (Continuous Integration)
- ✅ Lance automatiquement les tests à chaque push
- ✅ Vérifie la syntaxe PHP (PHP 8.4)
- ✅ Génère un rapport de couverture
- ✅ Valide composer.json
- ✅ Tests avec MySQL 8.0

### 2. Déploiement SSH
- ✅ Installe les dépendances de production
- ✅ Crée un package optimisé
- ✅ Déploie via rsync sur votre serveur
- ✅ Configure automatiquement le .env
- ✅ Notifications de succès/échec

### 3. Déploiement Docker
- ✅ Build automatique de l'image Docker
- ✅ Push sur Docker Hub avec versioning
- ✅ Cache intelligent pour builds rapides
- ✅ Déploiement automatique sur le serveur
- ✅ Rollback facile via tags

## 🔐 Secrets à configurer (IMPORTANT !)

Dans **GitHub > Settings > Secrets and variables > Actions** :

```bash
# Pour déploiement SSH (deploy.yml)
SSH_PRIVATE_KEY       # Clé SSH privée pour accéder au serveur
REMOTE_HOST          # IP ou domaine du serveur (ex: 123.45.67.89)
REMOTE_USER          # Utilisateur SSH (ex: ubuntu, www-data)
REMOTE_PATH          # Chemin de déploiement (ex: /var/www/crm)

# Pour la base de données
DB_HOST              # Hôte MySQL (ex: localhost)
DB_PORT              # Port MySQL (ex: 3306)
DB_NAME              # Nom de la base (ex: crm_production)
DB_USER              # Utilisateur MySQL
DB_PASSWORD          # Mot de passe MySQL

# Pour déploiement Docker (deploy-docker.yml)
DOCKER_USERNAME      # Username Docker Hub
DOCKER_PASSWORD      # Token Docker Hub
```

## 🚀 Utilisation

### Déploiement automatique
```bash
# Push sur main = déploiement automatique
git add .
git commit -m "feat: nouvelle fonctionnalité"
git push origin main

# GitHub Actions va automatiquement:
# 1. Lancer les tests
# 2. Si tests OK, déployer
```

### Déploiement manuel
```
1. Aller dans "Actions" de votre repo GitHub
2. Choisir "Deploy to Production"
3. Cliquer "Run workflow"
4. Sélectionner production ou staging
```

### Déploiement par version (recommandé)
```bash
# Créer un tag de version
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0

# Déclenche automatiquement le déploiement
```

## 📊 Monitoring

### Voir les logs
```
GitHub > Actions > Workflow en cours > Cliquer sur les étapes
```

### Vérifier la santé de l'app
```bash
# Sur le serveur
cd /var/www/crm
./scripts/health-check.sh
```

## 🔄 Rollback rapide

### Avec SSH
```bash
ssh user@server
cd /var/www/crm
git checkout v1.0.0  # Version précédente
composer install --no-dev --optimize-autoloader
```

### Avec Docker
```bash
ssh user@server
docker stop crm_app && docker rm crm_app
docker run -d --name crm_app username/crm-app:v1.0.0
```

## 📈 Prochaines étapes

1. **Configurer les secrets** dans GitHub
2. **Préparer le serveur** (voir QUICK_START_DEPLOY.md)
3. **Faire un premier push** sur main
4. **Vérifier les logs** dans Actions
5. **Tester l'application** déployée

## 💡 Conseils

- ✅ Toujours tester localement avant de pusher : `make test`
- ✅ Utiliser des tags pour les versions stables : `v1.0.0`
- ✅ Monitorer les logs GitHub Actions après chaque déploiement
- ✅ Faire un backup de la BDD avant déploiement majeur
- ✅ Tester le script health-check en local d'abord

## 🆘 Aide

En cas de problème :
1. Consulter [docs/DEPLOYMENT.md](./DEPLOYMENT.md)
2. Vérifier les logs GitHub Actions
3. Lancer `./scripts/health-check.sh` sur le serveur
4. Vérifier que tous les secrets sont configurés

## 🎉 Prêt à déployer !

Tout est configuré. Il ne reste plus qu'à :
1. Ajouter les secrets dans GitHub
2. Préparer le serveur
3. Push sur main !

---

**Note** : Les workflows sont désactivés par défaut jusqu'à la première utilisation. 
Le premier push sur main les activera automatiquement.
