<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\CustomersFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\V1\CustomerCollection;
use App\Http\Resources\V1\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request)
    {
        $filter = new CustomersFilters();
        $queryItems = $filter->transform($request);

        $query = Customer::query()->where($queryItems);

        if ($request->query('includeInvoices')) {
            $query = $query->with('invoices');
        }

        $paginator = $query->paginate()->appends($request->query());

        return new CustomerCollection($paginator);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->customerService->createCustomer($request->validated());

        return new CustomerResource($customer);
    }

    public function show(Request $request, Customer $customer)
    {
        if ($request->query('includeInvoices')) {
            $customer = $customer->load('invoices');
        }

        return new CustomerResource($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer = $this->customerService->updateCustomer($customer, $request->validated());

        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->deleteCustomer($customer);

        return response()->noContent();
    }
}