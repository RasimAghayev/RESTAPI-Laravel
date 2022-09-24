<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'customer_id'=>$this->customer_id,
            'invoice_id'=>$this->invoice_id,
            'item_id'=>$this->item_id,
            'vin'=>$this->vin,
            'brend'=>$this->brend,
            'marka'=>$this->marka,
            'sayi'=>$this->sayi,
            'qiymeti'=>$this->qiymeti,
            'cemi'=>$this->cemi,
            'status'=>$this->status,
        ];
    }
}
