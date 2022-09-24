<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\CustomersFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\V1\CustomerCollection;
use App\Http\Resources\V1\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter= new CustomersFilters();
        $queryItems=$filter->transform($request);//[['column','operator','value']]
        $customers=Customer::where($queryItems);
        $includeInvoices=$request->query('includeInvoices');
        if($includeInvoices){
            $customers=$customers->with('invoices');
        }

//        \DB::enableQueryLog(); // Enable query log
//        $customers->get();
//        dd(\DB::getQueryLog()); // Show results of log
        return new CustomerCollection($customers->paginate()->appends($request->query()));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerRequest $request)
    {
        return new CustomerResource(Customer::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer,Request $request)
    {
        $includeInvoices=$request->query('includeInvoices');
        if($includeInvoices){
            $customers=$customer->with('invoices');
        }

//        \DB::enableQueryLog(); // Enable query log
//        $customers->get();
//        dd(\DB::getQueryLog()); // Show results of log
//        return new CustomerCollection($customers->paginate()->appends($request->query()));
//        return new CustomerResource($customer);
        return new CustomerResource($customers);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomerRequest  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
