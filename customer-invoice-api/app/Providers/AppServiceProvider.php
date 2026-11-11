<?php

namespace App\Providers;

use App\Contracts\CustomerRepositoryInterface;
use App\Contracts\InvoiceItemsRepositoryInterface;
use App\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Eloquent\EloquentCustomerRepository;
use App\Repositories\Eloquent\EloquentInvoiceItemsRepository;
use App\Repositories\Eloquent\EloquentInvoiceRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(InvoiceItemsRepositoryInterface::class, EloquentInvoiceItemsRepository::class);
    }

    public function boot(): void
    {
        //
    }
}