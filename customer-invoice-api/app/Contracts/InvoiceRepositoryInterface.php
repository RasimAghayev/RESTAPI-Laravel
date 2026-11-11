<?php

namespace App\Contracts;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function find(string $invoiceId): ?Invoice;
    public function create(array $data): Invoice;
    public function update(Invoice $invoice, array $data): Invoice;
    public function delete(Invoice $invoice): void;
}