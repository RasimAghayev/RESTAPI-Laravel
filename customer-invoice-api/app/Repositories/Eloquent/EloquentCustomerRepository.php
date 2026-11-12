<?php

namespace App\Repositories\Eloquent;

use App\Contracts\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Customer::query();

        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate();
    }

    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}