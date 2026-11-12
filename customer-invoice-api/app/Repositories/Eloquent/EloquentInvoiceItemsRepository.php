<?php

namespace App\Repositories\Eloquent;

use App\Contracts\InvoiceItemsRepositoryInterface;
use App\Models\InvoiceItems;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentInvoiceItemsRepository implements InvoiceItemsRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = InvoiceItems::query();

        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate();
    }

    public function find(int $id): ?InvoiceItems
    {
        return InvoiceItems::find($id);
    }

    public function create(array $data): InvoiceItems
    {
        return InvoiceItems::create($data);
    }

    public function update(InvoiceItems $item, array $data): InvoiceItems
    {
        $item->update($data);

        return $item->fresh();
    }

    public function delete(InvoiceItems $item): void
    {
        $item->delete();
    }
}