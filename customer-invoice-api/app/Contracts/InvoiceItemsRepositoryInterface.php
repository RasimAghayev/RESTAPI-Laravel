<?php

namespace App\Contracts;

use App\Models\InvoiceItems;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceItemsRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function find(int $id): ?InvoiceItems;
    public function create(array $data): InvoiceItems;
    public function update(InvoiceItems $item, array $data): InvoiceItems;
    public function delete(InvoiceItems $item): void;
}