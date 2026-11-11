<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\InvoiceItemsFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceItemsRequest;
use App\Http\Resources\V1\InvoiceItemsCollection;
use App\Http\Resources\V1\InvoiceItemsResource;
use App\Models\InvoiceItems;
use App\Services\InvoiceItemsService;
use Illuminate\Http\Request;

class InvoiceItemsController extends Controller
{
    public function __construct(
        protected InvoiceItemsService $invoiceItemsService
    ) {}

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
        $item = $this->invoiceItemsService->createItem($request->validated());

        return new InvoiceItemsResource($item);
    }

    public function show(Request $request, InvoiceItems $invoiceItem)
    {
        return new InvoiceItemsResource($invoiceItem);
    }
}