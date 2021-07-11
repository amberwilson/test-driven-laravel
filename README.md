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

`php artisan migrate:refresh --seed`

`php artisan serve`

# IDE Helper
[Laravel IDE Helper Generator](https://github.com/barryvdh/laravel-ide-helper) is used to generate helper files for better autocompletion. Use the following commands to update the files.

## PHPDocs for Models
`php artisan ide-helper:models --write-mixin`

## PhpStorm Meta File
`php artisan ide-helper:meta`

## PHPDocs for Laravel Facades
`php artisan ide-helper:generate`
