<?php

namespace App\Services;

use App\Models\InvoiceItems;

class InvoiceItemsService
{
    public function createItem(array $data): InvoiceItems
    {
        $data['cemi'] = $data['sayi'] * $data['qiymeti'];

        return InvoiceItems::create($data);
    }

    public function updateItem(InvoiceItems $item, array $data): InvoiceItems
    {
        if (isset($data['sayi']) || isset($data['qiymeti'])) {
            $data['cemi'] = ($data['sayi'] ?? $item->sayi) * ($data['qiymeti'] ?? $item->qiymeti);
        }

        $item->update($data);

        return $item->fresh();
    }

    public function deleteItem(InvoiceItems $item): void
    {
        $item->delete();
    }
}