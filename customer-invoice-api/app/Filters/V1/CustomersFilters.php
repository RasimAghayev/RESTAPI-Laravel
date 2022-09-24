<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class CustomersFilters extends ApiFilter
{
    /*protected $allowedParms=[
      'postCode'=>['eq','gt','lt']
    ];*/
    protected $safeParms=[
        'name'=>['eq'],
        'type'=>['eq'], //Individual or Business
        'email'=>['eq'],
        'address'=>['eq'],
        'city'=>['eq'],
        'state'=>['eq'],
        'postalCode'=>['eq'],
    ];
    protected $columnMap=[
        'postalCode'=>'postal_code'
    ];
}
