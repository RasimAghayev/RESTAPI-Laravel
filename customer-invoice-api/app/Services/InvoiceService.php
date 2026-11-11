<?php

namespace App\Services;

use App\Contracts\InvoiceItemsRepositoryInterface;
use App\Contracts\InvoiceRepositoryInterface;
use App\Models\Invoice;
use Illuminate\Support\Collection;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepository,
        protected InvoiceItemsRepositoryInterface $invoiceItemsRepository
    ) {}

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
        $invoice->recalculateTotals();
    }

    public function createInvoice(array $data, Collection $items): Invoice
    {
        $totals = $this->calculateTotals($items);

        $invoice = $this->invoiceRepository->create(array_merge($data, $totals));

        foreach ($items as $item) {
            $item['invoice_id'] = $invoice->invoice_id;
            $item['cemi'] = $item['sayi'] * $item['qiymeti'];
            $this->invoiceItemsRepository->create($item);
        }

        return $invoice->fresh();
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return $this->invoiceRepository->update($invoice, $data);
    }
}