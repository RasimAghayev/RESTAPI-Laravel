<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public function customer(){
        return $this->belongTo(Customer::class);
    }


    public function invoiceitems(){
        return $this->hasMany(InvoiceItems::class,'invoice_id','invoice_id');
    }
}
