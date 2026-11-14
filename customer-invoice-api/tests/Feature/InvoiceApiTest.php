<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::factory()->create();
    }

    private function validInvoicePayload(int $customerId, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customerId,
            'invoice_id' => 'INV00001',
            'status' => 'Billed',
            'invoice_date' => '2026-01-01',
            'invoice_due_date' => '2026-01-31',
            'items' => [[
                'item_id' => 1,
                'vin' => 'VIN12345678901234',
                'brend' => 'BrandA',
                'marka' => 'ModelA',
                'sayi' => 2,
                'qiymeti' => 100,
                'status' => 1,
            ]],
        ], $overrides);
    }

    /** @testdox TC_INVOICE_INDEX_001 GET /api/v1/invoices lists invoices */
    public function test_invoice_index_001_lists_invoices(): void
    {
        $customer = $this->createCustomer();
        Invoice::factory()->create(['customer_id' => $customer->id, 'invoice_id' => 'INVLIST1']);

        $response = $this->getJson('/api/v1/invoices');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    /** @testdox TC_INVOICE_STORE_001 POST /api/v1/invoices creates an invoice with items */
    public function test_invoice_store_001_creates_invoice_with_items(): void
    {
        $customer = $this->createCustomer();

        $response = $this->postJson('/api/v1/invoices', $this->validInvoicePayload($customer->id));

        $response->assertStatus(200)
            ->assertJsonPath('data.invoice_id', 'INV00001');

        $this->assertDatabaseHas('invoices', ['invoice_id' => 'INV00001', 'customer_id' => $customer->id]);
        $this->assertDatabaseHas('invoice_items', ['invoice_id' => 'INV00001', 'cemi' => 200]);
    }

    /** @testdox TC_INVOICE_STORE_002 Validation fails when required fields are missing */
    public function test_invoice_store_002_validation_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/v1/invoices', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id', 'invoice_id', 'status', 'invoice_date', 'invoice_due_date', 'items']);
    }

    /** @testdox TC_INVOICE_STORE_003 Validation fails for an invalid status and customer */
    public function test_invoice_store_003_validation_fails_for_invalid_status_and_customer(): void
    {
        $response = $this->postJson('/api/v1/invoices', $this->validInvoicePayload(999999, ['status' => 'BAD']));

        $response->assertStatus(422)->assertJsonValidationErrors(['customer_id', 'status']);
    }

    /** @testdox TC_INVOICE_STORE_004 Validation fails when the due date is before the invoice date */
    public function test_invoice_store_004_validation_fails_when_due_date_before_invoice_date(): void
    {
        $customer = $this->createCustomer();

        $response = $this->postJson('/api/v1/invoices', $this->validInvoicePayload($customer->id, [
            'invoice_date' => '2026-02-01',
            'invoice_due_date' => '2026-01-01',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['invoice_due_date']);
    }

    /** @testdox TC_INVOICE_STORE_005 Validation fails for a duplicate invoice_id */
    public function test_invoice_store_005_validation_fails_for_duplicate_invoice_id(): void
    {
        $customer = $this->createCustomer();
        Invoice::factory()->create(['customer_id' => $customer->id, 'invoice_id' => 'INVDUP001']);

        $response = $this->postJson('/api/v1/invoices', $this->validInvoicePayload($customer->id, [
            'invoice_id' => 'INVDUP001',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['invoice_id']);
    }

    /** @testdox TC_INVOICE_STORE_006 Validation fails when the items array is empty */
    public function test_invoice_store_006_validation_fails_when_items_empty(): void
    {
        $customer = $this->createCustomer();

        $response = $this->postJson('/api/v1/invoices', $this->validInvoicePayload($customer->id, [
            'items' => [],
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['items']);
    }

    /** @testdox TC_INVOICE_SHOW_001 GET /api/v1/invoices/{id} returns the invoice */
    public function test_invoice_show_001_returns_invoice(): void
    {
        $customer = $this->createCustomer();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'invoice_id' => 'INVSHOW1']);

        $response = $this->getJson('/api/v1/invoices/'.$invoice->id);

        $response->assertStatus(200)->assertJsonPath('data.invoice_id', 'INVSHOW1');
    }

    /** @testdox TC_INVOICE_SHOW_002 GET /api/v1/invoices/{id} returns 404 for a missing invoice */
    public function test_invoice_show_002_returns_404_for_missing_invoice(): void
    {
        $response = $this->getJson('/api/v1/invoices/999999');

        $response->assertStatus(404);
    }

    /** @testdox TC_INVOICE_UPDATE_001 PUT /api/v1/invoices/{id} updates the invoice status */
    public function test_invoice_update_001_updates_invoice_status(): void
    {
        $customer = $this->createCustomer();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'invoice_id' => 'INVUPD01', 'status' => 'Billed']);

        $response = $this->putJson('/api/v1/invoices/'.$invoice->id, ['status' => 'Paid']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'Paid']);
    }

    /** @testdox TC_INVOICE_DELETE_001 DELETE /api/v1/invoices/{id} deletes the invoice */
    public function test_invoice_delete_001_deletes_invoice(): void
    {
        $customer = $this->createCustomer();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'invoice_id' => 'INVDEL01']);

        $response = $this->deleteJson('/api/v1/invoices/'.$invoice->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }
}
