<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class InvoicesFilters extends ApiFilter
{
    /*protected $allowedParms=[
      'postCode'=>['eq','gt','lt']
    ];
    */
    protected $safeParms=[
        'customerId'=>['eq'],
        'invoiceId'=>['eq'],
        'status'=>['eq','ne'],
        'invoiceDate'=>['eq','lt','lte','gt','gte','ne'],
        'invoiceDueDate'=>['eq','lt','lte','gt','gte','ne'],
        'subTotal'=>['eq','lt','lte','gt','gte'],
        'percentage'=>['eq','lt','lte','gt','gte'],
        'percentageAmount'=>['eq','lt','lte','gt','gte','ne'],
        'totalAfterpercentage'=>['eq','lt','lte','gt','gte','ne'],
        'amountPaid'=>['eq','lt','lte','gt','gte','ne'],
        'amountDue'=>['eq','lt','lte','gt','gte','ne'],
    ];
    protected $columnMap=[
        'customerId'=>'customer_id',
        'invoiceId'=>'invoice_id',
        'invoiceDate'=>'invoice_date',
        'invoiceDueDate'=>'invoice_due_date',
    ];

}
