# RESTAPI-Laravel

## Command 

composer global require laravel/installer

laravel new customer-invoice-api


or 

composer create-project laravel/laravel customer-invoice-api

## Model

php artisan make:model Customer --all
php artisan make:model Invoice --all
php artisan make:model InvoiceItems --all


php artisan migrate:fresh --seed