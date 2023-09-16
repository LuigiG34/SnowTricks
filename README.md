# Snowtricks 1.0
[![SymfonyInsight](https://insight.symfony.com/projects/a3edf830-112d-42be-81b5-3cc73cc8286d/big.svg)](https://insight.symfony.com/projects/a3edf830-112d-42be-81b5-3cc73cc8286d)

### 1. Requirements
1. Docker
2. Docker compose
3. Composer

### 2. Installation
1. Start the containers : ```docker compose up -d```
2. Install dependencies : ```composer install```
3. Create database : ```docker compose exec app_snowtricks php bin/console doctrine:database:create```
4. Create a migration : ```docker compose exec app_snowtricks php bin/console make:migration```
5. Apply migration to the database : ```docker compose exec app_snowtricks php bin/console doctrine:migrations:migrate```
6. Import data fixtures : ```docker compose exec app_snowtricks php bin/console hautelook:fixtures:load```

### 3. Access
1. Access to the UI : ```http:/localhost:8000```
2. Access to PhpMyAdmin : ```http:/localhost:8080``` credentials : username : root | password : password
3. Connect to user account : ```http:/localhost:8000/login``` credentials provided in the credentials.txt
