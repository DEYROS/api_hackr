# TP HackR 👨🏻‍💻

[Repo de l'intervenant Kevin Niel](https://github.com/kevinniel/M1-MDS-2425-API)

## Comment installer :

1. Clonez le dépôt :

   ```bash
   git clone https://github.com/DEYROS/api_hackr.git
   ```

2. Installez les dépendances :

   ```bash
   composer install
   ```

   ou

   ```bash
   composer update
   ```

3. Copiez le fichier `.env.example` pour créer le fichier `.env` :

   ```bash
   cp .env.example .env
   ```

4. Générez la clé de l'application :

   ```bash
   php artisan key:generate
   ```

5. Effectuez la migration de la base de données avec par défaut un utilisateur avec toutes les fonctionnalitées et qui est admin :

   ```bash
   php artisan migrate --seed
   ```

6. Lancez le serveur :
   ```bash
   php artisan serve
   ```

### Projet accessible :

🔗 [https://hackr.nertyrp.fr](https://hackr.nertyrp.fr)
Pour utiliser Swagger il faut dans un premier temps se connecter via /api/auth/login, récupérer le access_token et le mettre dans le bouton "Authorize".
---

## Description :

TP HackR est une API développée sous Laravel 11, offrant des outils de simulation de sécurité via une connexion sécurisée par JWT. Elle propose des fonctionnalités variées comme la vérification d’adresses email, un générateur d’identités fictives, et un service de phishing IA. Conçue en respectant les conventions RESTful, l’API inclut un système de droits administratifs modulables et un suivi des actions par logs, accessible aux administrateurs. La documentation est générée via Swagger et testée sur Postman pour garantir fiabilité et sécurité. Un administrateur peut attribuer des fonctionnalités aux utilisateurs, par défaut un nouvel utilisateur ne peut pas utiliser les fonctionnalités mises à disposition !

---

## Fonctionnalités 🔧

- 🔑 Générateur de mot de passe sécurisé ✔️
- 🔐 Vérification si un mot de passe est sur la liste des plus courants ([10k-most-common.txt](https://github.com/danielmiessler/SecLists/blob/master/Passwords/Common-Credentials/10k-most-common.txt)) ✔️
- 💥 DDoS ✔️
- 🔍 Outil de vérification d'existence d'adresse mail ✔️
- 📧 Spammer de mail (contenu + nombre d'envoi) ✔️
- 🕵️‍♀️ Crawler d'informations sur une personne (à partir d'un nom / prénom) ✔️
- 🖼️ Image random de quelqu'un qui n'existe pas ✔️
- 🌐 Récupérer tous les domaines & sous-domaines associés à un Nom De Domaine ✔️
- 👤 Génération d'identité fictive (utilisez la librairie [Faker PHP](https://fakerphp.org/) !) : ✔️
- 🕵️‍♂️ Service de phishing (création d'une page web de phishing sur mesure, backé sur de l'IA)

---

## Obligations 📜

- 🔒 Contrôler l'accès à l'API grâce à un système de connexion basé sur JWT ✔️
- 🛡️ Mettre en place un système de droits gérable par des administrateurs, permettant de définir quelles fonctionnalités peuvent être utilisées par quel utilisateur ✔️
- 📋 Système de logs interne à l'API, consultable uniquement par les admins, pour suivre :
  - Les dernières actions réalisées ✔️
  - Les dernières actions d'un utilisateur spécifique ✔️
  - Les dernières actions d'une fonctionnalité spécifique ✔️
- 📏 Respect strict des conventions RESTful ✔️
- 📑 Intégrer `Swagger` pour la documentation ✔️
- 📊 Respecter le modèle de maturité de Richardson ✔️
- 🧪 Tester l'API via POSTMAN, incluant :
  - Organisation des routes en collections dans un projet ✔️
  - Automatisation de la génération du token Bearer (JWT) et sa transmission dans toutes les requêtes ✔️
