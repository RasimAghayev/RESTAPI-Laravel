<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItems;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceItemsApiTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Customer, 1: Invoice} */
    private function seedInvoice(): array
    {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'invoice_id' => 'INVITM01']);

        return [$customer, $invoice];
    }

    /** @testdox TC_INVOICEITEMS_INDEX_001 GET /api/v1/invoiceitems lists items */
    public function test_invoiceitems_index_001_lists_items(): void
    {
        [$customer, $invoice] = $this->seedInvoice();
        InvoiceItems::factory()->create(['invoice_id' => $invoice->invoice_id]);

        $response = $this->getJson('/api/v1/invoiceitems');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    /** @testdox TC_INVOICEITEMS_STORE_001 POST /api/v1/invoiceitems creates an item and computes cemi */
    public function test_invoiceitems_store_001_creates_item_with_valid_payload(): void
    {
        [$customer, $invoice] = $this->seedInvoice();

        $response = $this->postJson('/api/v1/invoiceitems', [
            'invoice_id' => $invoice->invoice_id,
            'item_id' => 10,
            'vin' => 'VINITEM0000000001',
            'brend' => 'BrandX',
            'marka' => 'ModelX',
            'sayi' => 3,
            'qiymeti' => 50,
            'status' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.cemi', 150);

        $this->assertDatabaseHas('invoice_items', ['invoice_id' => $invoice->invoice_id, 'item_id' => 10, 'cemi' => 150]);
    }

    /** @testdox TC_INVOICEITEMS_STORE_002 Validation fails when required fields are missing */
    public function test_invoiceitems_store_002_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/invoiceitems', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoice_id', 'item_id', 'vin', 'brend', 'marka', 'sayi', 'qiymeti']);
    }

    /** @testdox TC_INVOICEITEMS_STORE_003 Validation fails for a nonexistent invoice */
    public function test_invoiceitems_store_003_validation_fails_for_nonexistent_invoice(): void
    {
        $response = $this->postJson('/api/v1/invoiceitems', [
            'invoice_id' => 'NOPE1234',
            'item_id' => 1,
            'vin' => 'VINITEM0000000002',
            'brend' => 'B',
            'marka' => 'M',
            'sayi' => 1,
            'qiymeti' => 10,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['invoice_id']);
    }

    /** @testdox TC_INVOICEITEMS_SHOW_001 GET /api/v1/invoiceitems/{id} returns the item */
    public function test_invoiceitems_show_001_returns_item(): void
    {
        [$customer, $invoice] = $this->seedInvoice();
        $item = InvoiceItems::factory()->create(['invoice_id' => $invoice->invoice_id]);

        $response = $this->getJson('/api/v1/invoiceitems/'.$item->id);

        $response->assertStatus(200)->assertJsonPath('data.item_id', $item->item_id);
    }

    /** @testdox TC_INVOICEITEMS_EDGE_001 Update and delete routes are not registered (405) */
    public function test_invoiceitems_edge_001_update_and_delete_routes_not_available(): void
    {
        [$customer, $invoice] = $this->seedInvoice();
        $item = InvoiceItems::factory()->create(['invoice_id' => $invoice->invoice_id]);

        $this->putJson('/api/v1/invoiceitems/'.$item->id, [])->assertStatus(405);
        $this->deleteJson('/api/v1/invoiceitems/'.$item->id)->assertStatus(405);
    }

    /** @testdox TC_INVOICEITEMS_EDGE_002 GET /api/v1/invoiceitems/{id} returns 404 for a missing item */
    public function test_invoiceitems_edge_002_show_missing_item_returns_404(): void
    {
        $response = $this->getJson('/api/v1/invoiceitems/999999');

        $response->assertStatus(404);
    }
}
