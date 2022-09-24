<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return array(
            'customer_id'=>$this->customer_id,
            'invoice_id'=>$this->invoice_id,
            'status'=>$this->status,//Billed, Paid, Void
            'invoice_date'=>$this->invoice_date,
            'invoice_due_date'=>$this->invoice_due_date,
            'subTotal'=>$this->subTotal,
            'percentage'=>$this->percentage,
            'percentageAmount'=>$this->percentageAmount,
            'totalAfterpercentage'=>$this->totalAfterpercentage,
            'amountPaid'=>$this->amountPaid,
            'amountDue'=>$this->amountDue,
            'invoiceitems'=>InvoiceItemsResource::collection($this->whenLoaded('invoiceitems')),
        );
    }
}
