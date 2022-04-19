# test-driven-laravel

Work for the [Test Driven Laravel course](https://course.testdrivenlaravel.com/)

# Install

`cp .env.example .env`

Update settings in `.env` to match your environment.

# Running

## Vue Frontend

`npm install`

`npm run watch`

## API

`php artisan migrate:fresh && php artisan db:seed`

`php artisan serve`

# IDE Helper

[Laravel IDE Helper Generator](https://github.com/barryvdh/laravel-ide-helper) is used to generate helper files for
better autocompletion. Use the following commands to update the files.

## PHPDocs for Models

IDE helper uses the DB to create the model documentation so all migrations will need to have been run for this to work.
This will need to be run after each table change.

`php artisan migrate:fresh && php artisan db:seed`

`php artisan ide-helper:models --write-mixin`

## PhpStorm Meta File

`php artisan ide-helper:meta`

## PHPDocs for Laravel Facades

`php artisan ide-helper:generate`

# Running Dusk Browser Tests

Note: Create .env.dusk.local first with a separate DB to avoid the main DB being wiped on each run.

`artisan dusk`
