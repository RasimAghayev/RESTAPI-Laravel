<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function createCustomer(array $data): Customer
    {
        return Customer::create($data);
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh();
    }

    public function deleteCustomer(Customer $customer): void
    {
        $customer->delete();
    }
}