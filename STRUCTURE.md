# Structure complète du projet CRM/Devis/Facture

## 📊 Statistiques
- **67 fichiers PHP** créés
- **3 couches** : Domain, Application, Infrastructure
- **3 domaines** : CRM, Billing, Banking
- **Architecture DDD** complète

## 📁 Arborescence détaillée

```
project/
├── docker/
│   ├── Dockerfile              # Image PHP 8.2 avec Apache
│   ├── apache.conf             # Configuration Apache
│   └── init.sql                # Schéma de base de données
│
├── public/
│   ├── index.php               # Point d'entrée (Router)
│   └── .htaccess               # URL rewriting
│
├── src/
│   ├── Domain/                 # 🔷 COUCHE DOMAINE (Logique métier pure)
│   │   ├── CRM/
│   │   │   ├── Entity/
│   │   │   │   ├── Personne.php
│   │   │   │   ├── Adresse.php
│   │   │   │   └── Contact.php
│   │   │   ├── ValueObject/
│   │   │   │   ├── PersonneId.php
│   │   │   │   ├── AdresseId.php
│   │   │   │   ├── ContactId.php
│   │   │   │   ├── TypePersonne.php (Enum)
│   │   │   │   ├── Email.php
│   │   │   │   └── Telephone.php
│   │   │   └── Repository/
│   │   │       └── PersonneRepositoryInterface.php
│   │   │
│   │   ├── Billing/
│   │   │   ├── Entity/
│   │   │   │   ├── Devis.php
│   │   │   │   ├── LigneDevis.php
│   │   │   │   ├── Facture.php
│   │   │   │   └── LigneFacture.php
│   │   │   ├── ValueObject/
│   │   │   │   ├── DevisId.php
│   │   │   │   ├── FactureId.php
│   │   │   │   ├── Montant.php
│   │   │   │   ├── StatutDevis.php (Enum)
│   │   │   │   └── StatutFacture.php (Enum)
│   │   │   └── Repository/
│   │   │       ├── DevisRepositoryInterface.php
│   │   │       └── FactureRepositoryInterface.php
│   │   │
│   │   └── Banking/
│   │       ├── Entity/
│   │       │   ├── CompteBancaire.php
│   │       │   └── OperationBancaire.php
│   │       ├── ValueObject/
│   │       │   ├── CompteBancaireId.php
│   │       │   ├── OperationBancaireId.php
│   │       │   ├── IBAN.php
│   │       │   └── TypeOperation.php (Enum)
│   │       └── Repository/
│   │           ├── CompteBancaireRepositoryInterface.php
│   │           └── OperationBancaireRepositoryInterface.php
│   │
│   ├── Application/            # 🔷 COUCHE APPLICATION (Use Cases)
│   │   ├── CRM/
│   │   │   ├── Command/
│   │   │   │   ├── CreatePersonneCommand.php
│   │   │   │   └── UpdatePersonneCommand.php
│   │   │   └── Service/
│   │   │       └── PersonneService.php
│   │   │
│   │   ├── Billing/
│   │   │   ├── Command/
│   │   │   │   ├── CreateDevisCommand.php
│   │   │   │   ├── AddLigneDevisCommand.php
│   │   │   │   └── CreateFactureCommand.php
│   │   │   └── Service/
│   │   │       ├── DevisService.php
│   │   │       └── FactureService.php
│   │   │
│   │   └── Banking/
│   │       ├── Command/
│   │       │   └── CreateCompteBancaireCommand.php
│   │       └── Service/
│   │           └── CompteBancaireService.php
│   │
│   └── Infrastructure/         # 🔷 COUCHE INFRASTRUCTURE (Implémentation)
│       ├── Persistence/
│       │   ├── Database.php
│       │   ├── CRM/
│       │   │   └── PDOPersonneRepository.php
│       │   ├── Billing/
│       │   │   ├── PDODevisRepository.php
│       │   │   └── PDOFactureRepository.php
│       │   └── Banking/
│       │       └── PDOCompteBancaireRepository.php
│       │
│       └── Web/
│           ├── Controller/
│           │   ├── AbstractController.php
│           │   ├── HomeController.php
│           │   ├── PersonneController.php
│           │   ├── DevisController.php
│           │   └── FactureController.php
│           │
│           └── View/
│               ├── layout/
│               │   ├── header.php
│               │   └── footer.php
│               ├── home/
│               │   └── index.php
│               ├── personne/
│               │   ├── index.php
│               │   ├── create.php
│               │   └── view.php
│               ├── devis/
│               │   ├── index.php
│               │   ├── create.php
│               │   └── view.php
│               ├── facture/
│               │   ├── index.php
│               │   ├── create.php
│               │   └── view.php
│               └── error.php
│
├── tests/
│   └── Domain/
│       ├── CRM/
│       │   ├── Entity/
│       │   │   └── PersonneTest.php
│       │   └── ValueObject/
│       │       └── EmailTest.php
│       ├── Billing/
│       │   ├── Entity/
│       │   │   └── DevisTest.php
│       │   └── ValueObject/
│       │       └── MontantTest.php
│       └── Banking/
│           └── Entity/
│               └── CompteBancaireTest.php
│
├── docker-compose.yml          # Configuration Docker (PHP, MySQL, phpMyAdmin)
├── Makefile                    # Commandes automatisées
├── composer.json               # Dépendances PHP
├── phpunit.xml                 # Configuration PHPUnit
├── .gitignore                  # Fichiers à ignorer
└── README.md                   # Documentation

```

## 🎯 Fonctionnalités implémentées

### ✅ Domaine CRM
- [x] Création de personnes physiques et morales
- [x] Gestion des adresses multiples
- [x] Gestion des contacts (email, téléphone)
- [x] CRUD complet avec interface web
- [x] Repository PDO avec transactions

### ✅ Domaine Billing
- [x] Création de devis avec lignes
- [x] Workflow de statuts (brouillon → envoyé → accepté/refusé)
- [x] Calcul automatique des montants
- [x] Interface web pour gestion des devis
- [x] Création de factures (structure complète)
- [ ] Interface web factures (en développement)

### ✅ Domaine Banking
- [x] Gestion des comptes bancaires avec IBAN
- [x] Opérations bancaires (débit/crédit)
- [x] Rattachement aux factures
- [ ] Interface web (à développer)

### ✅ Tests
- [x] Tests unitaires des entités principales
- [x] Tests des value objects
- [x] Tests du workflow métier
- [x] Configuration PHPUnit complète

### ✅ Infrastructure
- [x] Docker Compose (PHP 8.2, MySQL 8.0, phpMyAdmin)
- [x] Makefile avec commandes pratiques
- [x] Base de données avec schéma complet
- [x] Repositories PDO avec transactions
- [x] Router simple SSR
- [x] Contrôleurs et vues HTML

## 🚀 Démarrage rapide

```bash
# 1. Initialisation complète
make init

# 2. L'application est accessible sur :
# - App: http://localhost:8080
# - phpMyAdmin: http://localhost:8081

# 3. Lancer les tests
make test
```

## 📝 Commandes utiles

```bash
make help          # Liste toutes les commandes
make start         # Démarrer
make stop          # Arrêter
make logs          # Voir les logs
make shell         # Shell PHP
make db-shell      # Shell MySQL
make db-reset      # Réinitialiser la BDD
```

## 🏆 Points forts du projet

1. **Architecture DDD pure** : Séparation stricte des couches
2. **Typage strict PHP 8.2** : Utilisation d'enums, readonly properties
3. **Value Objects immutables** : Garantie de l'intégrité des données
4. **Tests unitaires** : Sans framework, testant la logique métier
5. **Repository Pattern** : Abstraction de la persistance
6. **Transactions SQL** : Cohérence des données
7. **Docker** : Environnement reproductible
8. **SSR simple** : Pas de dépendance JS, HTML pur
