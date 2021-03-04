# Projet 7-API BileMo

Conception d'une API pour BileMo, une entreprise offrant toute une sélection de téléphones mobiles haut de gamme.

Les diagrammes UML demandés `UML_Diagrammes/` se trouvent à la racine du projet.

## Environnement utilisé durant le développement
* [Symfony 5.2.3](https://symfony.com/doc/current/setup.html) 
* [Composer 2.0.9](https://getcomposer.org/doc/00-intro.md)
* MAMP 6 (985)
    * Apache 2.4.46
    * PHP 7.3.21
    * MySQL 5.7.30

## Installation
1- Clonez le repository GitHub dans le dossier voulu :
```
    git clone https://github.com/ProfesseurOrme/Projet-7-BileMo.git
```

2- Placez vous dans le répertoire de votre projet et installez les dépendances du projet avec la commande de [Composer](https://getcomposer.org/doc/00-intro.md) :
```
    composer install
```

3- Configurez vos variables d'environnement dans le fichier `.env` tel que :

* La connexion à la base de données  :
```
    DATABASE_URL=mysql://db.username:db.password@127.0.0.1:3306/api_bilemo
```

4- Si le fichier `.env` est correctement configuré, créez la base de données avec la commande ci-dessous :
```
    php bin/console doctrine:database:create
```
5- Créez les différentes tables de la base de données :
```
    php bin/console doctrine:migrations:migrate
```
6- Installer des données fictives avec des fixtures pour agrémenter l'api :
```
    php bin/console doctrine:fixtures:load
```
7- L'api est sécurisé par via un gestionnaire de Tokens. Pour le paramétrer et générer les clés, entrez les commandes 
suivantes à partir 
de la racine du projet :
```
    mkdir config/jwt
    openssl genrsa -out config/jwt/private.pem -aes256 4096
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```
Un des commandes doit vous demander de saisir une "passphrase" : veuillez entrer "apiphone" dans l'invite de 
commande. Veuillez saisir le "passphrase" dans le fichier .env (ligne 33) :
```
    JWT_PASSPHRASE=apiphone
```

10- Votre projet est prêt à l'utilisation ! Pour utiliser l'application dans un environnement local, veuillez vous
 renseigner sur cette
 [documentation](https://symfony.com/doc/current/setup.html#running-symfony-applications).
Pour utiliser l'api et ses ressources, une documentation est mise à disposition dans l'application dans :
```
    https://127.0.0.1:8000/api/doc
```