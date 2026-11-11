<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItems;
use Illuminate\Support\Collection;

class InvoiceService
{
    public function calculateTotals(Collection $items): array
    {
        $subTotal = $items->sum('cemi');
        $percentage = 0;
        $percentageAmount = 0;
        $totalAfterPercentage = $subTotal;
        $amountPaid = 0;
        $amountDue = $totalAfterPercentage;

        return [
            'subTotal' => $subTotal,
            'percentage' => $percentage,
            'percentageAmount' => $percentageAmount,
            'totalAfterpercentage' => $totalAfterPercentage,
            'amountPaid' => $amountPaid,
            'amountDue' => $amountDue,
        ];
    }

    public function recalculateFromItems(Invoice $invoice): void
    {
        $items = $invoice->invoiceitems;
        $totals = $this->calculateTotals($items);

        $invoice->update($totals);
    }

    public function createInvoice(array $data, Collection $items): Invoice
    {
        $totals = $this->calculateTotals($items);

        $invoice = Invoice::create(array_merge($data, $totals));

        foreach ($items as $item) {
            $item['invoice_id'] = $invoice->invoice_id;
            $item['customer_id'] = $invoice->customer_id;
            $item['cemi'] = $item['sayi'] * $item['qiymeti'];
            InvoiceItems::create($item);
        }

        return $invoice->fresh();
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice->fresh();
    }
}