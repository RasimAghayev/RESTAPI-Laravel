<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreInvoiceItemsRequest;
use App\Http\Requests\UpdateInvoiceItemsRequest;
use App\Models\InvoiceItems;
use App\Http\Controllers\Controller;

class InvoiceItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceItems  $invoiceItems
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceItems $invoiceItems)
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
