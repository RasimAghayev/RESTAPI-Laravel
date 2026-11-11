<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\InvoicesFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\V1\InvoiceCollection;
use App\Http\Resources\V1\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $filter = new InvoicesFilters();
        $queryItems = $filter->transform($request);

        $query = Invoice::query()->where($queryItems);

        if ($request->query('includeItems')) {
            $query = $query->with('invoiceitems');
        }

        $paginator = $query->paginate()->appends($request->query());

        return new InvoiceCollection($paginator);
    }

    public function store(StoreInvoiceRequest $request)
    {
        return new InvoiceResource(Invoice::create($request->all()));
    }

    public function show(Request $request, Invoice $invoice)
    {
        if ($request->query('includeItems')) {
            $invoice = $invoice->load('invoiceitems');
        }

        return new InvoiceResource($invoice);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $invoice->update($request->all());

        return new InvoiceResource($invoice->fresh());
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->noContent();
    }
}