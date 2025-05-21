# Backend Fintech API

Ce projet est une API backend développée avec Laravel pour une application mobile fintech. Il fournit des fonctionnalités essentielles pour la gestion des utilisateurs, des transactions et des recharges.

## Fonctionnalités

### Authentification
- Inscription des utilisateurs
- Connexion avec génération de token Sanctum
- Déconnexion avec révocation du token

### Gestion des Utilisateurs
- Création de compte avec informations personnelles
- Gestion du solde virtuel
- Profil utilisateur

### Transactions
- Historique des transactions
- Détails des transactions (montant, type, statut, date)
- Suivi des transactions en temps réel

### Recharges
- Recharge du solde virtuel
- Historique des recharges
- Statut des recharges

## Architecture Technique

### Technologies Utilisées
- Laravel 10.x
- PHP 8.x
- MySQL
- Laravel Sanctum pour l'authentification API

### Structure du Projet
```
backend_fintech/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── TransactionController.php
│   │   │   └── UserController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Transaction.php
│   │   └── Recharge.php
│   └── Services/
├── database/
│   └── migrations/
│       ├── create_users_table.php
│       ├── create_transactions_table.php
│       └── create_recharges_table.php
└── routes/
    └── api.php
```

### Base de Données
- Table `users`: Informations utilisateurs et solde
- Table `transactions`: Historique des transactions
- Table `recharges`: Gestion des recharges

## Installation

1. Cloner le repository
```bash
git clone [URL_DU_REPO]
```

2. Installer les dépendances
```bash
composer install
```

3. Configurer l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

4. Configurer la base de données dans le fichier `.env`

5. Exécuter les migrations
```bash
php artisan migrate
```

6. Démarrer le serveur
```bash
php artisan serve
```

## API Endpoints

### Authentification
- `POST /api/register` - Inscription
- `POST /api/login` - Connexion
- `POST /api/logout` - Déconnexion

### Utilisateurs
- `GET /api/user` - Profil utilisateur
- `GET /api/user/balance` - Solde utilisateur

### Transactions
- `GET /api/transactions` - Historique des transactions
- `GET /api/transactions/{id}` - Détails d'une transaction

### Recharges
- `POST /api/recharges` - Créer une recharge
- `GET /api/recharges` - Historique des recharges

## Sécurité
- Authentification via Laravel Sanctum
- Protection CSRF
- Validation des données
- Rate limiting sur les endpoints sensibles

## Contribution
Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

## Licence
Ce projet est sous licence MIT.
