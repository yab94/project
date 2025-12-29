# 🔐 Configuration des Secrets GitHub - Guide Complet

## 📍 Où configurer les secrets

1. Va sur ton repo GitHub : `https://github.com/yab94/project`
2. Clique sur **Settings** (en haut à droite)
3. Dans le menu de gauche, clique sur **Secrets and variables** > **Actions**
4. Clique sur **New repository secret** pour chaque secret

## 🔑 Secrets requis

### Pour le déploiement SSH (`deploy.yml`)

#### 1. SSH_PRIVATE_KEY
**Générer la clé SSH :**
```bash
# Sur ta machine locale
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy

# N'entre PAS de passphrase (laisse vide pour automation)
# Appuie sur Entrée 2 fois
```

**Copier la clé publique sur le serveur :**
```bash
# Méthode 1 : Automatique
ssh-copy-id -i ~/.ssh/github_deploy.pub utilisateur@ton-serveur.com

# Méthode 2 : Manuelle
cat ~/.ssh/github_deploy.pub
# Copie le contenu, puis sur le serveur :
ssh utilisateur@ton-serveur.com
mkdir -p ~/.ssh
nano ~/.ssh/authorized_keys
# Colle la clé publique, sauvegarde et quitte
chmod 600 ~/.ssh/authorized_keys
```

**Copier la clé PRIVÉE dans GitHub :**
```bash
cat ~/.ssh/github_deploy
```
- Copie TOUT le contenu (de `-----BEGIN` à `-----END`)
- Dans GitHub, crée un secret nommé `SSH_PRIVATE_KEY`
- Colle le contenu complet
- Clique sur **Add secret**

#### 2. REMOTE_HOST
L'adresse de ton serveur :
```
Exemples :
- 123.45.67.89           (IP publique)
- mon-serveur.com        (Nom de domaine)
- 192.168.1.100          (IP locale si serveur local)
```

**Valeur dans GitHub :**
```
Nom: REMOTE_HOST
Valeur: ton-ip-ou-domaine
```

#### 3. REMOTE_USER
L'utilisateur SSH sur ton serveur :
```
Exemples courants :
- ubuntu    (sur serveur Ubuntu)
- debian    (sur serveur Debian)
- root      (déconseillé en production)
- www-data  (pour Nginx/Apache)
```

**Comment le trouver :**
```bash
# Sur le serveur
whoami
```

**Valeur dans GitHub :**
```
Nom: REMOTE_USER
Valeur: ubuntu (ou ton utilisateur)
```

#### 4. REMOTE_PATH
Le chemin où déployer l'application :
```
Exemple recommandé : /var/www/crm
```

**Créer le répertoire sur le serveur :**
```bash
ssh utilisateur@serveur
sudo mkdir -p /var/www/crm
sudo chown $USER:$USER /var/www/crm
chmod 755 /var/www/crm
```

**Valeur dans GitHub :**
```
Nom: REMOTE_PATH
Valeur: /var/www/crm
```

### Pour la base de données

#### 5. DB_HOST
L'hôte de la base de données :
```
En production :
- localhost          (MySQL sur le même serveur)
- 127.0.0.1         (même chose)
- db.example.com    (serveur MySQL distant)
- 10.0.0.5          (IP privée d'un serveur MySQL)
```

**Valeur dans GitHub :**
```
Nom: DB_HOST
Valeur: localhost (ou ton hôte MySQL)
```

#### 6. DB_PORT
Le port MySQL :
```
Port par défaut : 3306
```

**Valeur dans GitHub :**
```
Nom: DB_PORT
Valeur: 3306
```

#### 7. DB_NAME
Le nom de la base de données en production :
```
Exemple : crm_production
```

**Créer la base sur le serveur :**
```bash
ssh utilisateur@serveur
mysql -u root -p

# Dans MySQL :
CREATE DATABASE crm_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Valeur dans GitHub :**
```
Nom: DB_NAME
Valeur: crm_production
```

#### 8. DB_USER
L'utilisateur MySQL :
```
Exemple : crm_user
```

**Créer l'utilisateur sur le serveur :**
```bash
mysql -u root -p

# Dans MySQL :
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'ton_mot_de_passe_fort';
GRANT ALL PRIVILEGES ON crm_production.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Valeur dans GitHub :**
```
Nom: DB_USER
Valeur: crm_user
```

#### 9. DB_PASSWORD
Le mot de passe MySQL :
```
⚠️ IMPORTANT : Utilise un mot de passe FORT !
```

**Générer un mot de passe sécurisé :**
```bash
# Sur Linux/Mac
openssl rand -base64 32
```

**Valeur dans GitHub :**
```
Nom: DB_PASSWORD
Valeur: ton_mot_de_passe_généré
```

### Pour le déploiement Docker (optionnel)

#### 10. DOCKER_USERNAME
Ton nom d'utilisateur Docker Hub :
```
1. Créer un compte sur https://hub.docker.com
2. Noter ton username
```

**Valeur dans GitHub :**
```
Nom: DOCKER_USERNAME
Valeur: ton_username_dockerhub
```

#### 11. DOCKER_PASSWORD
Token d'accès Docker Hub (PAS ton mot de passe) :
```
1. Aller sur https://hub.docker.com
2. Account Settings > Security > Access Tokens
3. Cliquer sur "New Access Token"
4. Description : "GitHub Actions"
5. Permissions : Read, Write, Delete
6. Copier le token (tu ne le verras qu'une fois !)
```

**Valeur dans GitHub :**
```
Nom: DOCKER_PASSWORD
Valeur: ton_access_token
```

## ✅ Vérification finale

### Liste des secrets à configurer :

**Obligatoires pour déploiement SSH :**
- [ ] SSH_PRIVATE_KEY
- [ ] REMOTE_HOST
- [ ] REMOTE_USER  
- [ ] REMOTE_PATH
- [ ] DB_HOST
- [ ] DB_PORT
- [ ] DB_NAME
- [ ] DB_USER
- [ ] DB_PASSWORD

**Optionnels pour Docker :**
- [ ] DOCKER_USERNAME
- [ ] DOCKER_PASSWORD

### Tester la connexion SSH

Avant de configurer GitHub, teste que ça fonctionne :

```bash
# Avec ta nouvelle clé
ssh -i ~/.ssh/github_deploy utilisateur@serveur

# Une fois connecté
cd /var/www/crm
ls -la
# Tu devrais avoir les permissions d'écriture
```

## 🎯 Après configuration

Une fois tous les secrets configurés :

```bash
# Sur ta machine locale
git add .
git commit -m "feat: add GitHub Actions deployment"
git push origin main

# ✨ Le déploiement se lancera automatiquement !
```

## 🔍 Voir les secrets configurés

Dans GitHub :
- Settings > Secrets and variables > Actions
- Tu verras la liste (mais pas les valeurs, c'est normal !)

## ❓ FAQ

**Q: Puis-je tester sans serveur de production ?**
R: Oui ! Tu peux déployer sur localhost. Configure `REMOTE_HOST=localhost` et `REMOTE_USER` avec ton user local.

**Q: Dois-je configurer les secrets Docker si je n'utilise pas Docker ?**
R: Non, seulement si tu veux utiliser `deploy-docker.yml`.

**Q: Comment changer un secret après ?**
R: GitHub > Settings > Secrets > Clique sur le secret > Update

**Q: Les secrets sont-ils sécurisés ?**
R: Oui, GitHub les chiffre et ne les affiche jamais dans les logs.

## 🚨 Sécurité

⚠️ **NE JAMAIS** :
- Commit les secrets dans le code
- Partager les secrets publiquement
- Utiliser le même mot de passe partout

✅ **TOUJOURS** :
- Utiliser des mots de passe forts
- Utiliser des clés SSH dédiées
- Utiliser des tokens au lieu de mots de passe (Docker)
- Restreindre les permissions SSH au minimum

## 📞 Besoin d'aide ?

Si tu bloques sur une étape :
1. Vérifie les logs GitHub Actions
2. Teste la connexion SSH manuellement
3. Vérifie les permissions sur le serveur
4. Consulte `docs/DEPLOYMENT.md`
