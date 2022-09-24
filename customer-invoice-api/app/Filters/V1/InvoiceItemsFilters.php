<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class InvoiceItemsFilters extends ApiFilter
{
    /*protected $allowedParms=[
      'postCode'=>['eq','gt','lt']
    ];
    */
    protected $safeParms=[
        'customerId'=>['eq'],
        'invoiceId'=>['eq'],
        'itemId'=>['eq','lt','lte','gt','gte','ne'],
        'vin'=>['eq'],
        'brend'=>['eq'],
        'marka'=>['eq'],
        'sayi'=>['eq','lt','lte','gt','gte','ne'],
        'qiymeti'=>['eq','lt','lte','gt','gte','ne'],
        'cemi'=>['eq','lt','lte','gt','gte','ne'],
        'status'=>['eq','lt','lte','gt','gte','ne'],
    ];
    protected $columnMap=[
        'customerId'=>'customer_id',
        'invoiceId'=>'invoice_id',
        'itemId'=>'item_id',
    ];

}
