<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'required|string|max:8|unique:invoices,invoice_id',
            'status' => 'required|in:Billed,Paid,Void',
            'invoice_date' => 'required|date',
            'invoice_due_date' => 'required|date|after_or_equal:invoice_date',
            'percentage' => 'nullable|integer|min:0|max:100',
            'amountPaid' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.vin' => 'required|string|max:17',
            'items.*.brend' => 'required|string|max:255',
            'items.*.marka' => 'required|string|max:255',
            'items.*.sayi' => 'required|integer|min:1',
            'items.*.qiymeti' => 'required|integer|min:0',
            'items.*.status' => 'nullable|integer',
        ];
    }
}