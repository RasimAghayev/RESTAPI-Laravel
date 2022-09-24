<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\InvoiceItemsFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceItemsRequest;
use App\Http\Requests\UpdateInvoiceItemsRequest;
use App\Http\Resources\V1\InvoiceItemsCollection;
use App\Models\InvoiceItems;
use Illuminate\Http\Request;

class InvoiceItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter= new InvoiceItemsFilters();
        $queryItems=$filter->transform($request);//[['column','operator','value']]

        if (count($queryItems)==0){
            return new InvoiceItemsCollection(InvoiceItems::paginate());
        }else{
            $invoiceitems=InvoiceItems::where($queryItems)->paginate();
            return new InvoiceItemsCollection($invoiceitems->appends($request->query()));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInvoiceItemsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceItemsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceItems  $invoiceItems
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceItems $invoiceItems)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceItemsRequest  $request
     * @param  \App\Models\InvoiceItems  $invoiceItems
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceItemsRequest $request, InvoiceItems $invoiceItems)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceItems  $invoiceItems
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceItems $invoiceItems)
    {
        //
    }
}
