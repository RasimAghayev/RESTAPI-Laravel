<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItems;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 5 characterization tests for the critical API read paths.
 *
 * These are the only tests added before Phase 11. They lock in the current
 * behavior of the customers / invoices / invoice-items index endpoints so the
 * Phase 5 baseline has a reproducible behavior contract (docs/BASELINE.md).
 */
class ApiCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--force' => true])->run();
    }

    /** @see GET /api/v1/customers */
    public function test_customers_index_returns_successful_response(): void
    {
        Customer::factory()->create();

        $response = $this->get('/api/v1/customers');

        $response->assertStatus(200);
    }

    /** @see GET /api/v1/invoices */
    public function test_invoices_index_returns_successful_response(): void
    {
        Invoice::factory()->create();

        $response = $this->get('/api/v1/invoices');

        $response->assertStatus(200);
    }

    /** @see GET /api/v1/invoiceitems */
    public function test_invoiceitems_index_returns_successful_response(): void
    {
        Customer::factory()->create();
        Invoice::factory()->create();
        InvoiceItems::factory()->create();

        $response = $this->get('/api/v1/invoiceitems');

        $response->assertStatus(200);
    }
}
