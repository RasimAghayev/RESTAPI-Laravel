<?php

namespace App\Repositories\Eloquent;

use App\Contracts\InvoiceRepositoryInterface;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Invoice::query();

        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate();
    }

    public function find(string $invoiceId): ?Invoice
    {
        return Invoice::where('invoice_id', $invoiceId)->first();
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice->fresh();
    }

    public function delete(Invoice $invoice): void
    {
        $invoice->delete();
    }
}