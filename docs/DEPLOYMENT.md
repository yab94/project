# Guide de Déploiement GitHub Actions

Ce projet contient 3 workflows GitHub Actions pour automatiser les tests et le déploiement.

## 📋 Workflows disponibles

### 1. **CI - Tests & Quality** (`ci.yml`)
- **Déclenché sur** : Push sur `main`/`develop`, Pull Requests
- **Fait** :
  - Lance les tests PHPUnit avec MySQL
  - Vérifie la syntaxe PHP
  - Génère un rapport de couverture

### 2. **Deploy to Production** (`deploy.yml`)
- **Déclenché sur** : Push sur `main`, tags `v*`, ou manuellement
- **Fait** :
  - Installe les dépendances de production
  - Crée un package de déploiement
  - Déploie via SSH/rsync sur votre serveur

### 3. **Deploy via Docker** (`deploy-docker.yml`)
- **Déclenché sur** : Push sur `main`, tags `v*`, ou manuellement
- **Fait** :
  - Build l'image Docker
  - Push sur Docker Hub
  - Déploie le container sur le serveur

## 🔐 Secrets à configurer

Allez dans **Settings > Secrets and variables > Actions** de votre repo GitHub et ajoutez :

### Pour le déploiement SSH (deploy.yml)
```
SSH_PRIVATE_KEY       # Votre clé SSH privée pour accéder au serveur
REMOTE_HOST          # IP ou domaine du serveur (ex: 123.45.67.89)
REMOTE_USER          # Utilisateur SSH (ex: ubuntu, root, www-data)
REMOTE_PATH          # Chemin de déploiement (ex: /var/www/crm)
```

### Pour la base de données
```
DB_HOST              # Hôte MySQL (ex: localhost, 10.0.0.5)
DB_PORT              # Port MySQL (ex: 3306)
DB_NAME              # Nom de la base (ex: app_production)
DB_USER              # Utilisateur MySQL
DB_PASSWORD          # Mot de passe MySQL
```

### Pour le déploiement Docker (deploy-docker.yml)
```
DOCKER_USERNAME      # Votre username Docker Hub
DOCKER_PASSWORD      # Votre token/password Docker Hub
```

## 📝 Configuration détaillée

### 1. Générer une clé SSH

```bash
# Sur votre machine locale
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/deploy_key

# Copier la clé publique sur le serveur
ssh-copy-id -i ~/.ssh/deploy_key.pub user@your-server.com

# Copier la clé privée dans GitHub Secrets
cat ~/.ssh/deploy_key
# Coller le contenu dans GitHub Secret: SSH_PRIVATE_KEY
```

### 2. Préparer le serveur

```bash
# SSH sur votre serveur
ssh user@your-server.com

# Créer le répertoire de déploiement
sudo mkdir -p /var/www/crm
sudo chown $USER:$USER /var/www/crm

# Installer PHP 8.4 (si nécessaire)
sudo apt update
sudo apt install -y php8.4 php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml

# Installer Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Configurer Nginx/Apache pour pointer vers /var/www/crm/public
```

### 3. Configuration Nginx (exemple)

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/crm/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. Configuration Docker Hub

```bash
# Créer un compte sur hub.docker.com
# Créer un Access Token dans Account Settings > Security

# Ajouter dans GitHub Secrets:
DOCKER_USERNAME: votre_username
DOCKER_PASSWORD: votre_access_token
```

## 🚀 Utilisation

### Déploiement automatique
Tout push sur `main` déclenche automatiquement :
1. Tests (CI)
2. Déploiement (si tests OK)

### Déploiement manuel
1. Aller dans **Actions** de votre repo
2. Choisir le workflow "Deploy to Production"
3. Cliquer sur "Run workflow"
4. Sélectionner l'environnement (production/staging)

### Déploiement par tag
```bash
git tag -a v1.0.0 -m "Release 1.0.0"
git push origin v1.0.0
```

## 🔍 Monitoring

### Voir les logs de déploiement
1. Aller dans **Actions** de votre repo
2. Cliquer sur le workflow en cours
3. Voir les logs détaillés de chaque étape

### En cas d'échec
- Vérifier que tous les secrets sont configurés
- Vérifier les permissions SSH sur le serveur
- Vérifier que le chemin `REMOTE_PATH` existe
- Consulter les logs du workflow GitHub Actions

## 🔄 Rollback

En cas de problème, pour revenir à une version précédente :

```bash
# Sur le serveur
cd /var/www/crm
git checkout v1.0.0  # Version précédente qui fonctionnait
composer install --no-dev --optimize-autoloader
```

Ou avec Docker :
```bash
docker pull username/php-ddd-app:v1.0.0
docker stop php_ddd_app && docker rm php_ddd_app
docker run -d --name php_ddd_app username/php-ddd-app:v1.0.0
```

## 📊 Stratégies de déploiement

### Blue-Green Deployment (recommandé pour production)
```yaml
# Dans deploy.yml, ajouter :
- name: Deploy to green environment
  run: |
    rsync ... /var/www/crm-green/
    # Tester l'application
    # Si OK, switch Nginx vers green
    # Puis supprimer blue
```

### Canary Deployment
```yaml
# Déployer sur un sous-ensemble de serveurs d'abord
# Monitorer les métriques
# Si OK, déployer sur le reste
```

## 🧪 Tests avant déploiement

Pour tester localement avant de pusher :

```bash
# Lancer les tests
make test

# Vérifier le build Docker
docker build -f docker/Dockerfile -t crm-test .
docker run --rm crm-test php -v
```

## 📚 Ressources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Docker Hub](https://hub.docker.com/)
- [SSH Deploy Action](https://github.com/easingthemes/ssh-deploy)
