# TP HackR
[Repo de l'intervenant Kevin](https://github.com/kevinniel/M1-MDS-2425-API)

## Comment installer :
```git clone https://github.com/DEYROS/api_hackr.git```

```composer i```
```php artisan serve```

### Projet accessible :
https://hackr.nertyrp.fr



Description : API qui met à disposition des outils de "hacking".

## Fonctionnalités
Outil de vérification d'existence d'adresse mail
Spammer de mail (contenu + nombre d'envoi)
service de phising (création d'une page web de phishing sur mesure - backé sur de l'IA !)
Est-ce que le MDP est sur la liste des plus courants (https://github.com/danielmiessler/SecLists/blob/master/Passwords/Common-Credentials/10k-most-common.txt)
récupérer tous domaines & sous-domaines associés à un Nom De Domaine
DDoS
changement d'image random (trouver une API qui fait ça ^^)
Génération d'identité fictive => utilisez la lirairie Faker !
faker JS : https://fakerjs.dev/
faker PHP : https://fakerphp.org/
faker python : https://faker.readthedocs.io/en/master/
faker .NET : https://www.nuget.org/packages/Faker.Net/
faker JAVA : https://javadoc.io/doc/com.github.javafaker/javafaker/latest/com/github/javafaker/Faker.html
faker ruby : https://github.com/faker-ruby/faker
Crawler d'information sur une personne (à partir d'un nom / prénom)
Générateur de mot de passe sécurisé

## Obligations
Contrôller l'accès à votre API grâce à un système de connexion basé sur JWT
Mettre en place un système de droits, gérable par des administrateurs, qui permet de définir quelles fonctionnalités peuvent être utilisées par quel utilisateur
Vous allez mettre en place un système de logs, interne à l'API, et consultable uniquement par les admins, qui permet de savoir quelles sont :
les dernièrs actions réalisées
les dernières actions d'un utilisateur spécifique
les dernières actions d'une fonctionnalité spécifique
Respect scrupuleux des conventions RESTful
Intégrer un fichier Swagger.json pour la partie documentation. Le fichier doit être exploitable sur "https://swagger.io/tools/swagger-ui/"
Respecter le modèle de maturité de Richardson
Vous devrez obligatoirement tester votre API via POSTMAN. En y incluant :
Organiser vos routes en collection et dans un projet
Automatisant la génération du bearer et sa transmission dans toutes les requêtes. (Bearer = JWT)