<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItems extends Model
{
    use HasFactory;

    public function invoceiitemscustomer(){
        return $this->belongTo(Customer::class);
    }

    public function invoice(){
        return $this->belongTo(Invoice::class);
    }
}
