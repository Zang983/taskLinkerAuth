# Exercice pour la formation développeur PHP/Symfony d'Openclassrooms.

## Installation du projet 
- Cloner le repo
- Dans la racine du projet faire un "composer install" pour installer les dépendances
- Dans le fichier .env ou dans un fichier nouvellement créer .env.local ajouter la clé DATABASE_URL pour la base de données.
- Créer la BDD et les fixtures :  ```symfony console doctrine:database:create ``` puis  ``` symfony console doctrine:migrations:migrate ``` et enfin  ``` symfony console doctrine:fixtures:load ```
- Lancer le serveur symfony  ``` symfony serve ```

### Dépendances : 
- PHP V8
  
