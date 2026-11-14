<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    private function validCustomerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Doe',
            'type' => 'I',
            'email' => 'jane@example.com',
            'address' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'CA',
            'postal_code' => '90001',
        ], $overrides);
    }

    /** @testdox TC_CUSTOMER_INDEX_001 GET /api/v1/customers lists customers */
    public function test_customer_index_001_lists_customers(): void
    {
        Customer::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    /** @testdox TC_CUSTOMER_INDEX_002 includeInvoices eager-loads the relation */
    public function test_customer_index_002_includes_invoices_when_requested(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson('/api/v1/customers?includeInvoices=1');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $customer->id)
            ->assertJsonStructure(['data' => [['invoices']]]);
    }

    /** @testdox TC_CUSTOMER_STORE_001 POST /api/v1/customers creates a customer with a valid payload */
    public function test_customer_store_001_creates_customer_with_valid_payload(): void
    {
        $response = $this->postJson('/api/v1/customers', $this->validCustomerPayload());

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'jane@example.com')
            ->assertJsonPath('data.postalCode', '90001');

        $this->assertDatabaseHas('customers', ['email' => 'jane@example.com']);
    }

    /** @testdox TC_CUSTOMER_STORE_002 Validation fails when required fields are missing */
    public function test_customer_store_002_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/customers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'email', 'address', 'city', 'state', 'postal_code']);
    }

    /** @testdox TC_CUSTOMER_STORE_003 Validation fails for an invalid type and email */
    public function test_customer_store_003_validation_fails_for_invalid_type_and_email(): void
    {
        $response = $this->postJson('/api/v1/customers', $this->validCustomerPayload([
            'type' => 'X',
            'email' => 'not-email',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['type', 'email']);
    }

    /** @testdox TC_CUSTOMER_SHOW_001 GET /api/v1/customers/{id} returns the customer */
    public function test_customer_show_001_returns_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson('/api/v1/customers/'.$customer->id);

        $response->assertStatus(200)->assertJsonPath('data.id', $customer->id);
    }

    /** @testdox TC_CUSTOMER_SHOW_002 GET /api/v1/customers/{id} returns 404 for a missing customer */
    public function test_customer_show_002_returns_404_for_missing_customer(): void
    {
        $response = $this->getJson('/api/v1/customers/999999');

        $response->assertStatus(404);
    }

    /** @testdox TC_CUSTOMER_UPDATE_001 PUT /api/v1/customers/{id} updates the customer */
    public function test_customer_update_001_updates_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->putJson('/api/v1/customers/'.$customer->id, ['name' => 'Updated Name']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Updated Name']);
    }

    /** @testdox TC_CUSTOMER_UPDATE_002 PUT rejects an invalid email */
    public function test_customer_update_002_validation_rejects_invalid_email_on_update(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->putJson('/api/v1/customers/'.$customer->id, ['email' => 'bad']);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    /** @testdox TC_CUSTOMER_DELETE_001 DELETE /api/v1/customers/{id} deletes the customer */
    public function test_customer_delete_001_deletes_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson('/api/v1/customers/'.$customer->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    /** @testdox TC_CUSTOMER_EDGE_001 The index endpoint supports the name[eq] filter */
    public function test_customer_edge_001_index_supports_filtering(): void
    {
        Customer::factory()->create(['name' => 'Alice']);
        Customer::factory()->create(['name' => 'Bob']);

        $response = $this->getJson('/api/v1/customers?name[eq]=Alice');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Alice');
    }
}
