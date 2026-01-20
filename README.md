# Portail Universitaire de Gestion d'Étudiants (Gestion_etudiant_v1)

Ce projet est une solution complète et robuste, développée en PHP, pour la gestion administrative et pédagogique d'un établissement d'enseignement supérieur. Il offre une plateforme centralisée permettant aux administrateurs, aux professeurs et aux étudiants d'interagir avec les données scolaires, les notes, les emplois du temps et les informations de paiement.

L'objectif principal est de simplifier les processus de gestion académique en fournissant une interface intuitive et sécurisée pour chaque partie prenante.

## 🌟 Fonctionnalités Clés

Le système est structuré autour de trois profils d'utilisateurs distincts, chacun ayant accès à un ensemble de fonctionnalités spécifiques :

### 👨‍💼 Espace Administrateur (Gestion Scolaire)

L'administrateur est le pivot du système, responsable de la gestion globale des données de l'établissement.

| Catégorie | Fonctionnalités Détaillées |
| :--- | :--- |
| **Gestion des Utilisateurs** | Ajout, modification et suppression des fiches d'étudiants et de professeurs. |
| **Gestion Pédagogique** | Création et mise à jour des matières, et affectation des professeurs aux matières. |
| **Emplois du Temps** | Gestion des emplois du temps pour les différents niveaux (B1, B2, B3). |
| **Gestion Financière** | Suivi des versements des étudiants et possibilité de suppression des enregistrements de paiement. |
| **Rapports & Documents** | Génération de listes d'étudiants et de professeurs, ainsi que des reçus et relevés de notes. |

### 👩‍🏫 Espace Professeur

Les professeurs disposent d'un espace dédié pour gérer leur charge de travail et interagir avec les étudiants.

| Catégorie | Fonctionnalités Détaillées |
| :--- | :--- |
| **Gestion des Notes** | Saisie et modification des notes pour les contrôles continus (CC), les travaux pratiques (TP) et les examens. |
| **Suivi Pédagogique** | Consultation de la liste des cours qu'ils enseignent. |
| **Communication** | Envoi de conseils et de notes aux étudiants via le système. |
| **Sécurité** | Changement de mot de passe obligatoire lors de la première connexion. |

### 🧑‍🎓 Espace Étudiant

Les étudiants peuvent consulter toutes les informations pertinentes à leur scolarité.

| Catégorie | Fonctionnalités Détaillées |
| :--- | :--- |
| **Dossier Personnel** | Consultation de la fiche étudiant personnelle (`card.php`). |
| **Résultats Académiques** | Accès aux notes détaillées (CC, TP, Examen) pour toutes les matières. |
| **Ressources** | Consultation de la liste des cours. |
| **Interaction** | Accès à un chatbot et à des quiz pour l'apprentissage. |
| **Sécurité** | Changement de mot de passe obligatoire lors de la première connexion. |

## 🛠️ Technologies Utilisées

Ce projet est principalement construit sur une architecture LAMP (Linux, Apache, MySQL, PHP) :

*   **Langage de Programmation :** PHP (avec autoloading PSR-4)
*   **Base de Données :** MySQL
*   **Interface Utilisateur :** HTML5, CSS3, JavaScript
*   **Framework CSS :** Bootstrap 5.3.0
*   **Gestion des Dépendances :** Composer
*   **Librairies Clés :**
    *   `phpmailer` : Pour l'envoi d'e-mails.
    *   `mpdf`, `tcpdf`, `fpdi`, `pdfparser` : Pour la génération et la manipulation avancée de documents PDF (relevés, reçus).

##  Guide d'Installation

Pour déployer ce projet sur votre environnement local, suivez les étapes ci-dessous.

### Prérequis

Assurez-vous d'avoir les éléments suivants installés :

*   Un serveur web (Apache ou Nginx)
*   PHP (version 7.x ou supérieure recommandée)
*   MySQL ou MariaDB
*   Composer

### 1. Cloner le Dépôt

Ouvrez votre terminal et clonez le projet :

```bash
git clone https://github.com/hbadir-habinou/Gestion_etudiant_v1.git
cd Gestion_etudiant_v1
```

### 2. Configuration de la Base de Données

1.  Créez une base de données nommée `ecole` dans votre système de gestion de base de données (SGBD).
2.  Importez le schéma et les données initiales à partir du fichier `tables/ecole.sql`.

### 3. Mise à Jour des Informations de Connexion

Modifiez le fichier de connexion à la base de données pour qu'il corresponde à vos identifiants locaux.

Ouvrez le fichier `school/db_connect.php` et ajustez les valeurs des propriétés `$host`, `$db_name`, `$username` et `$password` si nécessaire :

```php
// school/db_connect.php
class Database {
    private $host = 'localhost';
    private $db_name = 'ecole';
    private $username = ''; // À modifier
    private $password = ''; // À modifier
    // ...
}
```

### 4. Installation des Dépendances PHP

Exécutez Composer pour installer les librairies requises (notamment pour la gestion des PDF et l'envoi d'e-mails) :

```bash
composer install
```

### 5. Accès à l'Application

Placez le dossier du projet dans le répertoire racine de votre serveur web (par exemple, `htdocs` ou `www`).

Accédez à l'application via votre navigateur :

```
http://localhost/Gestion_etudiant_v1/
```

## 🔑 Accès et Rôles

Le point d'entrée est la page de connexion (`index.php`), qui gère l'authentification pour les trois types d'utilisateurs.

| Rôle | Point d'Accès Initial | Comportement Spécifique |
| :--- | :--- | :--- |
| **Administrateur** | `index.php` | Redirection vers le tableau de bord (`dashboard.php`). |
| **Professeur** | `index.php` | Redirection vers la carte professeur (`CardProf.php`). Changement de mot de passe forcé à la première connexion. |
| **Étudiant** | `index.php` | Redirection vers la carte étudiant (`card.php`). Changement de mot de passe forcé à la première connexion. |

*Note : Les identifiants de connexion par défaut doivent être consultés directement dans le fichier `tables/ecole.sql` après l'importation.*

## 📂 Structure du Projet

Voici un aperçu des répertoires clés du projet :

| Répertoire | Description |
| :--- | :--- |
| `school/classes/` | Contient les classes PHP principales (modèles) pour la logique métier (`Login`, `Etudiant`, `Professeur`, etc.). |
| `school/client/` | Espace dédié aux étudiants (consultation des notes, cours, profil). |
| `school/professeur/` | Espace dédié aux professeurs (saisie des notes, gestion des cours). |
| `school/verification/` | Contient des librairies externes pour la vérification et la manipulation de PDF. |
| `tables/` | Contient le script SQL de la base de données (`ecole.sql`) et les fichiers CSV de données initiales. |
| `vendor/` | Dépendances PHP installées par Composer. |
| `index.php` | Le point d'entrée unique pour la connexion et l'authentification multi-rôles. |

## 🤝 Contribution

Les contributions sont les bienvenues ! Si vous souhaitez améliorer ce projet, veuillez soumettre une *pull request* ou ouvrir une *issue* sur ce dépôt.

