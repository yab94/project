# 🔐 Configuration des Secrets - Résumé

## 🎯 Objectif

Configurer les **11 secrets** nécessaires pour que GitHub Actions puisse déployer automatiquement ton application.

## 📚 Documentation disponible

| Fichier | Description |
|---------|-------------|
| **docs/SECRETS_SETUP.md** | Guide détaillé complet avec exemples |
| **scripts/setup-secrets.sh** | Script interactif pour générer les valeurs |
| Ce fichier | Résumé rapide |

## 🚀 Méthode rapide (recommandée)

```bash
# Lancer le script interactif
./scripts/setup-secrets.sh

# Le script va :
# ✓ Générer la clé SSH
# ✓ Copier la clé sur ton serveur
# ✓ Créer le répertoire de déploiement
# ✓ Générer un mot de passe sécurisé
# ✓ Te donner toutes les valeurs à copier dans GitHub
```

## 📋 Liste des secrets requis

### Obligatoires (9 secrets)

| # | Nom | Description | Exemple |
|---|-----|-------------|---------|
| 1 | `SSH_PRIVATE_KEY` | Clé SSH privée | Contenu de `~/.ssh/github_deploy` |
| 2 | `REMOTE_HOST` | Adresse du serveur | `123.45.67.89` ou `mon-serveur.com` |
| 3 | `REMOTE_USER` | Utilisateur SSH | `ubuntu`, `www-data` |
| 4 | `REMOTE_PATH` | Chemin de déploiement | `/var/www/crm` |
| 5 | `DB_HOST` | Hôte MySQL | `localhost` |
| 6 | `DB_PORT` | Port MySQL | `3306` |
| 7 | `DB_NAME` | Nom de la base | `crm_production` |
| 8 | `DB_USER` | Utilisateur MySQL | `crm_user` |
| 9 | `DB_PASSWORD` | Mot de passe MySQL | `un_mot_de_passe_fort` |

### Optionnels (2 secrets pour Docker)

| # | Nom | Description |
|---|-----|-------------|
| 10 | `DOCKER_USERNAME` | Username Docker Hub |
| 11 | `DOCKER_PASSWORD` | Access Token Docker Hub |

## 🔧 Configuration manuelle rapide

### 1. Générer la clé SSH (1 min)

```bash
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_deploy
# N'entre PAS de passphrase (laisse vide)
```

### 2. Copier sur le serveur (30 sec)

```bash
ssh-copy-id -i ~/.ssh/github_deploy.pub utilisateur@serveur
```

### 3. Copier la clé dans GitHub (1 min)

```bash
cat ~/.ssh/github_deploy
# Copier TOUT le contenu
```

Puis dans **GitHub > Settings > Secrets > New secret** :
- Name: `SSH_PRIVATE_KEY`
- Value: [Coller la clé]

### 4. Ajouter les autres secrets (2 min)

Créer chaque secret dans GitHub avec les valeurs de ton serveur.

## 🌐 Où ajouter les secrets dans GitHub

```
1. Va sur : https://github.com/yab94/project
2. Clique : Settings (en haut)
3. Menu gauche : Secrets and variables > Actions  
4. Bouton : New repository secret
5. Remplis : Name + Value
6. Clique : Add secret
7. Répète pour les 9 (ou 11) secrets
```

## ✅ Vérification

Une fois tous les secrets ajoutés, tu devrais voir :

```
Repository secrets (9)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✓ SSH_PRIVATE_KEY      Updated X days ago
✓ REMOTE_HOST          Updated X days ago
✓ REMOTE_USER          Updated X days ago
✓ REMOTE_PATH          Updated X days ago
✓ DB_HOST              Updated X days ago
✓ DB_PORT              Updated X days ago
✓ DB_NAME              Updated X days ago
✓ DB_USER              Updated X days ago
✓ DB_PASSWORD          Updated X days ago
```

## 🎬 Après configuration

### Test SSH manuel

```bash
ssh -i ~/.ssh/github_deploy utilisateur@serveur
cd /var/www/crm
# Si ça fonctionne, GitHub Actions fonctionnera aussi !
```

### Premier déploiement

```bash
git add .
git commit -m "feat: add GitHub Actions deployment"
git push origin main

# Va voir : GitHub > Actions
# Le workflow "CI" va se lancer automatiquement !
# Si les tests passent, "Deploy" se lance aussi !
```

## 🆘 Problèmes courants

### "Permission denied (publickey)"
→ La clé SSH n'est pas correctement copiée sur le serveur
```bash
ssh-copy-id -i ~/.ssh/github_deploy.pub utilisateur@serveur
```

### "mkdir: cannot create directory"
→ L'utilisateur n'a pas les permissions
```bash
ssh utilisateur@serveur
sudo mkdir -p /var/www/crm
sudo chown $USER:$USER /var/www/crm
```

### "Access denied for user"
→ L'utilisateur MySQL n'existe pas ou mauvais mot de passe
```bash
mysql -u root -p
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON crm_production.* TO 'crm_user'@'localhost';
```

## 📊 Timeline de configuration

| Étape | Durée | Action |
|-------|-------|--------|
| 1 | 1 min | Générer clé SSH |
| 2 | 1 min | Copier sur serveur |
| 3 | 2 min | Créer BDD MySQL |
| 4 | 5 min | Ajouter secrets dans GitHub |
| **Total** | **~10 min** | ✅ |

## 🎉 Une fois terminé

Tu n'auras plus jamais à refaire cette config !

À chaque push sur `main` :
1. ✅ Tests automatiques
2. ✅ Déploiement automatique
3. ✅ Application mise à jour en production

**C'est du CI/CD complet ! 🚀**

---

**Documentation complète** : `docs/SECRETS_SETUP.md`  
**Script interactif** : `./scripts/setup-secrets.sh`
