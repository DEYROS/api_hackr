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

5. Effectuez la migration de la base de données :

    ```bash
    php artisan migrate
    ```

6. Lancez le serveur :
    ```bash
    php artisan serve
    ```

### Projet accessible :

🔗 [https://hackr.nertyrp.fr](https://hackr.nertyrp.fr)

---

## Description :

TP HackR est une API développée sous Laravel 11, offrant des outils de simulation de sécurité via une connexion sécurisée par JWT. Elle propose des fonctionnalités variées comme la vérification d’adresses email, un générateur d’identités fictives, et un service de phishing IA. Conçue en respectant les conventions RESTful, l’API inclut un système de droits administratifs modulables et un suivi des actions par logs, accessible aux administrateurs. La documentation est générée via Swagger et testée sur Postman pour garantir fiabilité et sécurité.

---

## Fonctionnalités 🔧

-   🔑 Générateur de mot de passe sécurisé ✔️
-   🔐 Vérification si un mot de passe est sur la liste des plus courants ([10k-most-common.txt](https://github.com/danielmiessler/SecLists/blob/master/Passwords/Common-Credentials/10k-most-common.txt)) ✔️
-   🔍 Outil de vérification d'existence d'adresse mail
-   📧 Spammer de mail (contenu + nombre d'envoi)
-   🕵️‍♂️ Service de phishing (création d'une page web de phishing sur mesure, backé sur de l'IA)
-   🌐 Récupérer tous les domaines & sous-domaines associés à un Nom De Domaine
-   💥 DDoS
-   🖼️ Changement d'image random (trouver une API qui fait ça)
-   👤 Génération d'identité fictive (utilisez la librairie Faker !) :
    -   [Faker JS](https://fakerjs.dev/)
    -   [Faker PHP](https://fakerphp.org/)
    -   [Faker Python](https://faker.readthedocs.io/en/master/)
    -   [Faker .NET](https://www.nuget.org/packages/Faker.Net/)
    -   [Faker JAVA](https://javadoc.io/doc/com.github.javafaker/javafaker/latest/com/github/javafaker/Faker.html)
    -   [Faker Ruby](https://github.com/faker-ruby/faker)
-   🕵️‍♀️ Crawler d'informations sur une personne (à partir d'un nom / prénom)

---

## Obligations 📜

-   🔒 Contrôler l'accès à l'API grâce à un système de connexion basé sur JWT ✔️
-   🛡️ Mettre en place un système de droits gérable par des administrateurs, permettant de définir quelles fonctionnalités peuvent être utilisées par quel utilisateur ✔️
-   📋 Système de logs interne à l'API, consultable uniquement par les admins, pour suivre :
    -   Les dernières actions réalisées ✔️
    -   Les dernières actions d'un utilisateur spécifique ✔️
    -   Les dernières actions d'une fonctionnalité spécifique ✔️
-   📏 Respect strict des conventions RESTful ✔️
-   📑 Intégrer `Swagger` pour la documentation ✔️
-   📊 Respecter le modèle de maturité de Richardson
-   🧪 Tester l'API via POSTMAN, incluant :
    -   Organisation des routes en collections dans un projet ✔️
    -   Automatisation de la génération du token Bearer (JWT) et sa transmission dans toutes les requêtes ✔️
