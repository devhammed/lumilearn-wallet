# Lumilearn Wallet

The Lumilearn Wallet Platform.

## Technologies

- PHP 8.x
- Node.js 22.x
- Laravel 11.x
- MySQL 8.x

## Installation

The project uses the database as the storage system for models, cache, locks, sessions and queue jobs to simplify the installation.

You need to create MySQL databases for both development and testing environments e.g `lumilearn_wallet` and `lumilearn_wallet_test`.

Then run the following commands in your terminal:

```shell
$ git clone git@github.com:devhammed/lumilearn-wallet.git

$ cd lumilearn-wallet

$ composer install

$ cp .env.example .env # Configure for development environment e.g update the `DB_*` details

$ cp .env.example .env.testing # Configure for testing environment e.g update the `DB_*` details and change the `APP_ENV` to `testing`

$ php artisan migrate

$ php artisan storage:link

$ npm install

$ npm run build
```

You can then run `composer run dev` to start the development server or use `php artisan test` to run the tests.
