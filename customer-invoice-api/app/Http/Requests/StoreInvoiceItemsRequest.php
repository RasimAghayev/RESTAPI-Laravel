<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'item_id' => 'required|integer',
            'vin' => 'required|string|max:17',
            'brend' => 'required|string|max:255',
            'marka' => 'required|string|max:255',
            'sayi' => 'required|integer|min:1',
            'qiymeti' => 'required|integer|min:0',
            'status' => 'nullable|integer',
        ];
    }
}