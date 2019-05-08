# Symfony-Pre-Order-System
Simple pre-order system with symfony php framework

## Requirements
* PHP 7.1.3 or higher;
* PDO-SQLite PHP extension enabled;
* Composer

## Installation

Clone the repository with this command:
```
 $ git clone https://github.com/1anil21/Symfony-Pre-Order-System.git
```
Install Dependencies with this command:
```
 $ composer install
```
Configure DATABASE_URL in .env file such as:
```
DATABASE_URL=mysql://username:password@host:3306/database_name
```
Create database and tables using these commands below:
```
 $ php bin/console doctrine:database:create or ./bin/console doctrine:database:create
 $ php bin/console doctrine:migrations:migrate or ./php bin/console doctrine:migrations:migrate
```

Generate fake products using the command below:
```
 $ php bin/console doctrine:fixtures:load or ./bin/console doctrine:fixtures:load
```

To run server execute the command belw:
```
 $ php bin/console server:start or ./bin/console server:start
```

## Admin Panel

You can access admin panel from /admin url with this credentials:
username: admin
password: admin

New admins and products can be added and removed from the admin panel. Also pre-orders can be confirmed.

## Auto Reject

You can set a cron job for every hour in order to autoReject pre orders that have not confirmed in 24 hours.
Open crontab to edit with this command:
```
 $ crontab -e
```

Add a cron job line works every hour with this command:
```
0 * * * * php ~/www/preorder_api/bin/console preorder:autoreject --env=prod
```

## Tests

You can run unit tests with this command:
```
 $ php bin/phpunit or ./bin/phpunit
```
