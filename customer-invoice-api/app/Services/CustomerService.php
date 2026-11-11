<?php

namespace App\Services;

use App\Contracts\CustomerRepositoryInterface;
use App\Models\Customer;

class CustomerService
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository
    ) {}

    public function createCustomer(array $data): Customer
    {
        return $this->customerRepository->create($data);
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return $this->customerRepository->update($customer, $data);
    }

    public function deleteCustomer(Customer $customer): void
    {
        $this->customerRepository->delete($customer);
    }
}