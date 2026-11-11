<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'sometimes|exists:customers,id',
            'status' => 'sometimes|in:Billed,Paid,Void',
            'invoice_date' => 'sometimes|date',
            'invoice_due_date' => 'sometimes|date|after_or_equal:invoice_date',
            'percentage' => 'nullable|integer|min:0|max:100',
            'amountPaid' => 'nullable|integer|min:0',
        ];
    }
}