<?php

namespace App\Services;

use App\Contracts\InvoiceItemsRepositoryInterface;
use App\Models\InvoiceItems;

class InvoiceItemsService
{
    public function __construct(
        protected InvoiceItemsRepositoryInterface $invoiceItemsRepository
    ) {}

    public function createItem(array $data): InvoiceItems
    {
        $data['cemi'] = $data['sayi'] * $data['qiymeti'];

        return $this->invoiceItemsRepository->create($data);
    }

    public function updateItem(InvoiceItems $item, array $data): InvoiceItems
    {
        if (isset($data['sayi']) || isset($data['qiymeti'])) {
            $data['cemi'] = ($data['sayi'] ?? $item->sayi) * ($data['qiymeti'] ?? $item->qiymeti);
        }

        return $this->invoiceItemsRepository->update($item, $data);
    }

    public function deleteItem(InvoiceItems $item): void
    {
        $this->invoiceItemsRepository->delete($item);
    }
}