<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\InvoicesFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\V1\InvoiceCollection;
use App\Http\Resources\V1\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

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
        $data = $request->validated();
        $items = collect($data['items'] ?? []);
        unset($data['items']);

        $invoice = $this->invoiceService->createInvoice($data, $items);

        if ($request->query('includeItems')) {
            $invoice = $invoice->load('invoiceitems');
        }

        return new InvoiceResource($invoice);
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
        $data = $request->validated();

        $invoice = $this->invoiceService->updateInvoice($invoice, $data);

        if ($request->query('includeItems')) {
            $invoice = $invoice->load('invoiceitems');
        }

        return new InvoiceResource($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->noContent();
    }
}