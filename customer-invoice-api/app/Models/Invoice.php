<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'status',
        'invoice_date',
        'invoice_due_date',
        'subTotal',
        'percentage',
        'percentageAmount',
        'totalAfterpercentage',
        'amountPaid',
        'amountDue',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItems::class, 'invoice_id', 'invoice_id');
    }

    public function addItem(array $data): InvoiceItems
    {
        $data['invoice_id'] = $this->invoice_id;
        $data['customer_id'] = $this->customer_id;
        $data['cemi'] = $data['sayi'] * $data['qiymeti'];

        return $this->items()->create($data);
    }

    public function removeItem(InvoiceItems $item): void
    {
        if ($item->invoice_id !== $this->invoice_id) {
            throw new \InvalidArgumentException('Item does not belong to this invoice');
        }

        $item->delete();
    }

    public function recalculateTotals(): void
    {
        $subTotal = $this->items->sum('cemi');
        $percentage = $this->percentage ?? 0;
        $percentageAmount = $subTotal * $percentage / 100;
        $totalAfterPercentage = $subTotal + $percentageAmount;
        $amountPaid = $this->amountPaid ?? 0;
        $amountDue = $totalAfterPercentage - $amountPaid;

        $this->update([
            'subTotal' => $subTotal,
            'percentageAmount' => $percentageAmount,
            'totalAfterpercentage' => $totalAfterPercentage,
            'amountDue' => $amountDue,
        ]);
    }
}