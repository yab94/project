#!/bin/bash

# Script interactif pour configurer les secrets GitHub Actions
# Ce script génère les commandes à exécuter

set -e

echo "🔐 Configuration des Secrets GitHub Actions"
echo "==========================================="
echo ""
echo "Ce script va te guider pour configurer les secrets."
echo "Il ne configure RIEN automatiquement, il te donne juste les commandes."
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Fonction pour afficher les instructions
print_step() {
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}$1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

print_command() {
    echo -e "${YELLOW}$ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ ${NC}$1"
}

print_warning() {
    echo -e "${RED}⚠️  $1${NC}"
}

read -p "Appuie sur Entrée pour commencer..."
clear

# ==============================================================================
# 1. SSH KEY
# ==============================================================================
print_step "1/11 - Configuration de la clé SSH"
echo ""
echo "📝 Cette clé permet à GitHub Actions de se connecter à ton serveur."
echo ""

read -p "As-tu déjà une clé SSH pour GitHub Actions ? (y/n): " has_key

if [[ "$has_key" != "y" ]]; then
    echo ""
    echo "Génération d'une nouvelle clé SSH :"
    echo ""
    print_command "ssh-keygen -t ed25519 -C 'github-actions' -f ~/.ssh/github_deploy"
    echo ""
    echo "👉 N'entre PAS de passphrase (laisse vide pour automation)"
    echo ""
    read -p "Exécuter cette commande maintenant ? (y/n): " exec_ssh
    
    if [[ "$exec_ssh" == "y" ]]; then
        ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_deploy
        echo -e "${GREEN}✓ Clé générée !${NC}"
    fi
fi

echo ""
read -p "Quelle est l'adresse de ton serveur ? (ex: 123.45.67.89): " server_host
read -p "Quel est l'utilisateur SSH ? (ex: ubuntu): " server_user

echo ""
echo "Copie de la clé publique sur le serveur :"
echo ""
print_command "ssh-copy-id -i ~/.ssh/github_deploy.pub $server_user@$server_host"
echo ""
read -p "Exécuter cette commande maintenant ? (y/n): " exec_copy

if [[ "$exec_copy" == "y" ]]; then
    ssh-copy-id -i ~/.ssh/github_deploy.pub "$server_user@$server_host"
    echo -e "${GREEN}✓ Clé copiée sur le serveur !${NC}"
fi

echo ""
echo "Affichage de la clé PRIVÉE (à copier dans GitHub) :"
echo ""
print_command "cat ~/.ssh/github_deploy"
echo ""
read -p "Afficher la clé maintenant ? (y/n): " show_key

if [[ "$show_key" == "y" ]]; then
    echo -e "${YELLOW}━━━━━━━━━━ DÉBUT DE LA CLÉ ━━━━━━━━━━${NC}"
    cat ~/.ssh/github_deploy
    echo -e "${YELLOW}━━━━━━━━━━ FIN DE LA CLÉ ━━━━━━━━━━${NC}"
    echo ""
    echo "📋 Copie TOUT le contenu ci-dessus (de BEGIN à END)"
    echo ""
    echo "Dans GitHub :"
    echo "  1. Va sur Settings > Secrets and variables > Actions"
    echo "  2. New repository secret"
    echo "  3. Name: SSH_PRIVATE_KEY"
    echo "  4. Value: [Colle la clé complète]"
    echo "  5. Add secret"
fi

read -p "Appuie sur Entrée quand c'est fait..."
clear

# ==============================================================================
# 2-4. SERVER INFO
# ==============================================================================
print_step "2-4/11 - Informations du serveur"
echo ""

echo "Configuration détectée :"
echo -e "  ${GREEN}REMOTE_HOST${NC} = $server_host"
echo -e "  ${GREEN}REMOTE_USER${NC} = $server_user"
echo ""

read -p "Chemin de déploiement sur le serveur (ex: /var/www/crm): " remote_path
remote_path=${remote_path:-/var/www/crm}

echo ""
echo "Création du répertoire sur le serveur :"
echo ""
print_command "ssh $server_user@$server_host 'sudo mkdir -p $remote_path && sudo chown \$USER:\$USER $remote_path'"
echo ""
read -p "Exécuter cette commande ? (y/n): " exec_mkdir

if [[ "$exec_mkdir" == "y" ]]; then
    ssh "$server_user@$server_host" "sudo mkdir -p $remote_path && sudo chown \$USER:\$USER $remote_path"
    echo -e "${GREEN}✓ Répertoire créé !${NC}"
fi

echo ""
echo "📋 Secrets à ajouter dans GitHub :"
echo ""
echo -e "  ${GREEN}REMOTE_HOST${NC}  = $server_host"
echo -e "  ${GREEN}REMOTE_USER${NC}  = $server_user"
echo -e "  ${GREEN}REMOTE_PATH${NC}  = $remote_path"

read -p "Appuie sur Entrée quand c'est fait..."
clear

# ==============================================================================
# 5-9. DATABASE
# ==============================================================================
print_step "5-9/11 - Configuration de la base de données"
echo ""

read -p "Hôte MySQL sur le serveur (ex: localhost): " db_host
db_host=${db_host:-localhost}

read -p "Port MySQL (défaut: 3306): " db_port
db_port=${db_port:-3306}

read -p "Nom de la base de données (ex: crm_production): " db_name
db_name=${db_name:-crm_production}

read -p "Utilisateur MySQL (ex: crm_user): " db_user
db_user=${db_user:-crm_user}

echo ""
echo "Génération d'un mot de passe sécurisé..."
db_password=$(openssl rand -base64 24)
echo -e "Mot de passe généré : ${YELLOW}$db_password${NC}"
echo ""
read -p "Garder ce mot de passe ? (y/n, si n tu pourras entrer le tien): " keep_pwd

if [[ "$keep_pwd" != "y" ]]; then
    read -sp "Entre ton mot de passe MySQL: " db_password
    echo ""
fi

echo ""
echo "Création de la base et de l'utilisateur sur le serveur :"
echo ""
echo "Commandes à exécuter sur le serveur :"
echo ""
print_command "ssh $server_user@$server_host"
echo ""
print_command "mysql -u root -p"
echo ""
echo "Puis dans MySQL :"
echo ""
print_command "CREATE DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
print_command "CREATE USER '$db_user'@'$db_host' IDENTIFIED BY '$db_password';"
print_command "GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user'@'$db_host';"
print_command "FLUSH PRIVILEGES;"
print_command "EXIT;"
echo ""

read -p "As-tu exécuté ces commandes ? (y/n): " db_done

echo ""
echo "📋 Secrets à ajouter dans GitHub :"
echo ""
echo -e "  ${GREEN}DB_HOST${NC}      = $db_host"
echo -e "  ${GREEN}DB_PORT${NC}      = $db_port"
echo -e "  ${GREEN}DB_NAME${NC}      = $db_name"
echo -e "  ${GREEN}DB_USER${NC}      = $db_user"
echo -e "  ${GREEN}DB_PASSWORD${NC}  = $db_password"

read -p "Appuie sur Entrée quand c'est fait..."
clear

# ==============================================================================
# 10-11. DOCKER (OPTIONNEL)
# ==============================================================================
print_step "10-11/11 - Docker Hub (optionnel)"
echo ""

read -p "Veux-tu configurer le déploiement Docker ? (y/n): " use_docker

if [[ "$use_docker" == "y" ]]; then
    read -p "Username Docker Hub: " docker_user
    
    echo ""
    echo "Pour obtenir un Access Token :"
    echo "  1. Va sur https://hub.docker.com"
    echo "  2. Account Settings > Security > Access Tokens"
    echo "  3. New Access Token"
    echo "  4. Copie le token"
    echo ""
    
    read -sp "Token Docker Hub: " docker_token
    echo ""
    
    echo ""
    echo "📋 Secrets à ajouter dans GitHub :"
    echo ""
    echo -e "  ${GREEN}DOCKER_USERNAME${NC}  = $docker_user"
    echo -e "  ${GREEN}DOCKER_PASSWORD${NC}  = [ton token]"
else
    echo "OK, tu peux ignorer les workflows Docker."
fi

read -p "Appuie sur Entrée pour le résumé final..."
clear

# ==============================================================================
# RÉSUMÉ
# ==============================================================================
print_step "✅ Configuration terminée !"
echo ""
echo "📋 RÉSUMÉ - Secrets à ajouter dans GitHub"
echo ""
echo "GitHub > Settings > Secrets and variables > Actions > New repository secret"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}Nom${NC}               | ${GREEN}Valeur${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "SSH_PRIVATE_KEY   | [Contenu de ~/.ssh/github_deploy]"
echo "REMOTE_HOST       | $server_host"
echo "REMOTE_USER       | $server_user"
echo "REMOTE_PATH       | $remote_path"
echo "DB_HOST           | $db_host"
echo "DB_PORT           | $db_port"
echo "DB_NAME           | $db_name"
echo "DB_USER           | $db_user"
echo "DB_PASSWORD       | $db_password"
if [[ "$use_docker" == "y" ]]; then
    echo "DOCKER_USERNAME   | $docker_user"
    echo "DOCKER_PASSWORD   | [Ton token Docker]"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Sauvegarder dans un fichier (sans les mots de passe !)
cat > .github-secrets-summary.txt << EOF
# GitHub Secrets Configuration Summary
# Generated: $(date)

REMOTE_HOST=$server_host
REMOTE_USER=$server_user
REMOTE_PATH=$remote_path
DB_HOST=$db_host
DB_PORT=$db_port
DB_NAME=$db_name
DB_USER=$db_user
DB_PASSWORD=$db_password

# SSH Private Key location: ~/.ssh/github_deploy
EOF

echo "💾 Résumé sauvegardé dans : .github-secrets-summary.txt"
echo ""
print_warning "N'oublie pas d'ajouter ce fichier dans .gitignore !"
echo ""

echo "🚀 Prochaines étapes :"
echo ""
echo "  1. Ajouter tous les secrets dans GitHub"
echo "  2. Tester la connexion SSH :"
print_command "     ssh -i ~/.ssh/github_deploy $server_user@$server_host"
echo "  3. Pusher sur main :"
print_command "     git push origin main"
echo "  4. Regarder le déploiement dans GitHub Actions !"
echo ""
echo -e "${GREEN}✓ Configuration terminée !${NC}"
