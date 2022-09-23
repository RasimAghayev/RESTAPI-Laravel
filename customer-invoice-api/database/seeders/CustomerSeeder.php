<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Customer::factory()
                ->count(5)
                ->hasInvoices(10)
                ->hasCustomerInvoiceItems(2)
                ->create();
        Customer::factory()
                ->count(4)
                ->hasInvoices(3)
                ->hasCustomerInvoiceItems(2)
                ->create();
        Customer::factory()
                ->count(2)
                ->hasInvoices(5)
                ->hasCustomerInvoiceItems(2)
                ->create();
        Customer::factory()
                ->count(5)
                ->create();
    }
}
