# RESTAPI-Laravel
# POSTMAN

[![Run in Postman](https://run.pstmn.io/button.svg)](https://app.getpostman.com/run-collection/36cc1e266722a67ae009?action=collection%2Fimport)

[![Run in Postman](https://run.pstmn.io/button.svg)](https://www.postman.com/lunar-shadow-319654/workspace/rest-api-laravel/collection/1965733-cfed0a88-bac0-4191-a785-bcfce1750cb5?action=share&creator=1965733)


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

php artisan make:resource V1/CustomerResource
php artisan make:resource V1/InvoiceResource
php artisan make:resource V1/InvoiceItemsResource




php artisan make:resource V1/CustomerCollection
php artisan make:resource V1/InvoiceCollection
php artisan make:resource V1/InvoiceItemsCollection