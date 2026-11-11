<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'sometimes|exists:invoices,invoice_id',
            'customer_id' => 'sometimes|exists:customers,id',
            'item_id' => 'sometimes|integer',
            'vin' => 'sometimes|string|max:17',
            'brend' => 'sometimes|string|max:255',
            'marka' => 'sometimes|string|max:255',
            'sayi' => 'sometimes|integer|min:1',
            'qiymeti' => 'sometimes|integer|min:0',
            'status' => 'nullable|integer',
        ];
    }
}