# Lumilearn Wallet

The Lumilearn Wallet Platform.

## Technologies

- PHP 8.x
- Node.js 22.x
- Laravel 11.x
- MySQL 8.x

## Installation

You need to create MySQL databases for both development and testing environments e.g `lumilearn_wallet` for develop and `lumilearn_wallet_test` for testing.

The project uses the database for models, cache, locks, sessions and queue jobs storage system to simplify the installation.

Then run the following commands:

```shell
$ git clone git@github.com:devhammed/lumilearn-wallet.git

$ cd lumilearn-wallet

$ composer install

$ cp .env.example .env # Configure for development environment e.g update the database details

$ cp .env.example .env.testing # Configure for testing environment e.g update the database details

$ php artisan key:generate

$ php artisan migrate

$ php artisan storage:link

$ npm install

$ npm run build
```

You can then run `composer run dev` to start the development server or use `php artisan test` to run the tests.
