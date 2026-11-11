<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\InvoiceItemsFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceItemsRequest;
use App\Http\Requests\UpdateInvoiceItemsRequest;
use App\Http\Resources\V1\InvoiceItemsCollection;
use App\Http\Resources\V1\InvoiceItemsResource;
use App\Models\InvoiceItems;
use Illuminate\Http\Request;

class InvoiceItemsController extends Controller
{
    public function index(Request $request)
    {
        $filter = new InvoiceItemsFilters();
        $queryItems = $filter->transform($request);

        $query = InvoiceItems::query()->where($queryItems);

        $paginator = $query->paginate()->appends($request->query());

        return new InvoiceItemsCollection($paginator);
    }

    public function store(StoreInvoiceItemsRequest $request)
    {
        return new InvoiceItemsResource(InvoiceItems::create($request->all()));
    }

    public function show(Request $request, InvoiceItems $invoiceItem)
    {
        return new InvoiceItemsResource($invoiceItem);
    }

    public function update(UpdateInvoiceItemsRequest $request, InvoiceItems $invoiceItem)
    {
        $invoiceItem->update($request->all());

        return new InvoiceItemsResource($invoiceItem->fresh());
    }

    public function destroy(InvoiceItems $invoiceItem)
    {
        $invoiceItem->delete();

        return response()->noContent();
    }
}