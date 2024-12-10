# Lumilearn Wallet

The Lumilearn Wallet Platform.

## Technologies

- PHP 8.x
- Node.js 22.x
- Laravel 11.x
- MySQL 8.x

## Installation

```shell
$ git clone git@github.com:devhammed/lumilearn-wallet.git

$ cd lumilearn-wallet

$ composer install

$ cp .env.example .env # For development environment

$ cp .env.example .env.testing # For testing environment

$ php artisan key:generate

$ php artisan migrate

$ php artisan storage:link

$ npm install

$ npm run build
```

You can then run `composer run dev` to start the development server or use `php artisan test` to run the tests.
