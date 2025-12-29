# 🚀 Quick Start - GitHub Actions Deployment

## Configuration en 5 minutes

### 1️⃣ Configurer les Secrets GitHub

```bash
# Dans votre repo GitHub, allez dans:
# Settings > Secrets and variables > Actions > New repository secret

# Ajoutez ces secrets:
SSH_PRIVATE_KEY       # Votre clé SSH privée
REMOTE_HOST          # Ex: 123.45.67.89
REMOTE_USER          # Ex: ubuntu
REMOTE_PATH          # Ex: /var/www/crm
DB_HOST              # Ex: localhost
DB_PORT              # Ex: 3306
DB_NAME              # Ex: crm_production
DB_USER              # Ex: crm_user
DB_PASSWORD          # Votre mot de passe MySQL
```

### 2️⃣ Générer une clé SSH (si nécessaire)

```bash
# Sur votre machine locale
ssh-keygen -t ed25519 -C "github-deploy" -f ~/.ssh/github_deploy
ssh-copy-id -i ~/.ssh/github_deploy.pub user@your-server.com

# Copier la clé privée dans GitHub Secrets
cat ~/.ssh/github_deploy
```

### 3️⃣ Préparer le serveur

```bash
# SSH sur votre serveur
ssh user@your-server.com

# Créer le répertoire
sudo mkdir -p /var/www/crm
sudo chown $USER:$USER /var/www/crm

# Installer PHP 8.4 (Ubuntu/Debian)
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt install -y php8.4 php8.4-fpm php8.4-mysql \
                    php8.4-mbstring php8.4-xml php8.4-intl

# Installer Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installer Nginx
sudo apt install -y nginx

# Configurer Nginx (voir ci-dessous)
```

### 4️⃣ Configuration Nginx

```bash
# Créer le fichier de configuration
sudo nano /etc/nginx/sites-available/crm

# Coller cette configuration:
```

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

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Activer le site
sudo ln -s /etc/nginx/sites-available/crm /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5️⃣ Déployer !

```bash
# Option A: Push sur main (automatique)
git add .
git commit -m "Initial deployment"
git push origin main

# Option B: Déploiement manuel
# Aller dans Actions > Deploy to Production > Run workflow
```

## 🎯 Workflows disponibles

| Workflow | Quand | Fait quoi |
|----------|-------|-----------|
| **CI** | Push/PR | Lance les tests PHPUnit |
| **Deploy** | Push sur main | Déploie via SSH/rsync |
| **Deploy Docker** | Push sur main | Build + Push Docker |

## 🔍 Vérifier le déploiement

```bash
# Sur le serveur
cd /var/www/crm
./scripts/health-check.sh

# Localement
curl -I http://your-domain.com
```

## ❓ Troubleshooting

### Les tests échouent
```bash
# Vérifier localement
make test
```

### Le déploiement échoue
1. Vérifier que tous les secrets sont configurés
2. Vérifier la connexion SSH : `ssh user@server "ls -la"`
3. Vérifier les permissions : `ls -la /var/www/`

### L'application ne fonctionne pas
```bash
# Sur le serveur
cd /var/www/crm
./scripts/health-check.sh

# Vérifier les logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.4-fpm.log
```

## 📚 Documentation complète

Voir [docs/DEPLOYMENT.md](./DEPLOYMENT.md) pour la documentation complète.
