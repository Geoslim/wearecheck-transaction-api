
## Transaction Processing System API

### Prerequisites

- PHP v8.3

### Start up

To start project, perform the following steps in the order

- Clone the repository by running the command
- git clone 'https://github.com/Geoslim/wearecheck-transaction-api.git'
- cd wearecheck-transaction-api
- Run `composer install`
- Run `cp .env.example .env`
- Fill your configuration settings in the '.env' file you created above
- Run `php artisan key:generate`
- Run `php artisan migrate --seed`
