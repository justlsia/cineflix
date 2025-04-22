# CineFlix

CineFlix est une application web qui permet de consulter des fiches de films via l'API TMDB.

---

## Description

CineFlix offre une interface intuitive pour découvrir des films par genre (action, romance, etc.), rechercher des films par titre, ou encore consulter les films les mieux notés par les utilisateurs.

L’application interagit directement avec l’API TMDB pour récupérer les données, mais permet aussi d’enregistrer certaines fiches de films localement dans une base de données pour une consultation rapide ou un archivage.

---

## Fonctionnalités principales

-  Consultation de films par **genres**
-  Recherche de films par **titre**
-  Accès aux **films les mieux notés**
-  **Enregistrement local** de fiches de films dans une base de données

---

##  Technologies utilisées

- **Backend :** Symfony (PHP)
- **API :** [The Movie Database (TMDB)](https://www.themoviedb.org/)
- **Base de données :** MariaDB

---

### Prérequis

- PHP & Symfony installés sur votre environnement
- Une base de données **MariaDB**
- Une **clé API TMDB** (à générer sur [TMDB](https://developer.themoviedb.org/))

### Étapes d'installation

```bash
# 1. Cloner le projet
git clone https://github.com/justlsia/cineflix.git
cd cineflix

# 2. Installer les dépendances
composer install

# 3. Configuration de l'environnement
cp .env.example .env

# Modifier les variables suivantes dans le fichier .env :
# - DB credentials (utilisateur, mot de passe, nom de la BDD)
# - TMDB_API_KEY=VOTRE_CLE_API

# 4. Créer la base de données et les tables
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Lancer le serveur de développement
symfony server:start
```

---

## Captures d'écran

- Accueil

- Films par genre

- Fiche détaillée d’un film



