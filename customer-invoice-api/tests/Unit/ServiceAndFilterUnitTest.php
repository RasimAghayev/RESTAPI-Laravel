<?php

namespace Tests\Unit;

use App\Contracts\InvoiceItemsRepositoryInterface;
use App\Filters\ApiFilter;
use App\Models\InvoiceItems;
use App\Services\InvoiceItemsService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class ServiceAndFilterUnitTest extends TestCase
{
    /** @testdox TC_UNIT_INVOICE_SVC_001 InvoiceService::calculateTotals sums item cemi values */
    public function test_unit_invoice_svc_001_calculate_totals_sums_items(): void
    {
        $items = collect([
            ['sayi' => 2, 'qiymeti' => 100, 'cemi' => 200],
            ['sayi' => 1, 'qiymeti' => 50, 'cemi' => 50],
        ]);

        $totals = $this->app->make(InvoiceService::class)->calculateTotals($items);

        $this->assertSame(250, $totals['subTotal']);
        $this->assertSame(250, $totals['totalAfterpercentage']);
        $this->assertSame(250, $totals['amountDue']);
    }

    /** @testdox TC_UNIT_INVOICE_SVC_002 InvoiceService::calculateTotals returns zero for an empty collection */
    public function test_unit_invoice_svc_002_calculate_totals_returns_zero_for_empty_collection(): void
    {
        $totals = $this->app->make(InvoiceService::class)->calculateTotals(collect([]));

        $this->assertSame(0, $totals['subTotal']);
        $this->assertSame(0, $totals['amountDue']);
    }

    /** @testdox TC_UNIT_INVOICEITEMS_SVC_001 InvoiceItemsService::createItem computes cemi from sayi and qiymeti */
    public function test_unit_invoiceitems_svc_001_create_item_computes_cemi(): void
    {
        $repository = Mockery::mock(InvoiceItemsRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data): bool {
                return $data['cemi'] === 100;
            }))
            ->andReturn((new InvoiceItems)->forceFill(['sayi' => 4, 'qiymeti' => 25, 'cemi' => 100]));

        $this->app->instance(InvoiceItemsRepositoryInterface::class, $repository);

        $item = $this->app->make(InvoiceItemsService::class)->createItem([
            'invoice_id' => 'INV00001',
            'item_id' => 1,
            'vin' => 'VIN12345678901234',
            'brend' => 'B',
            'marka' => 'M',
            'sayi' => 4,
            'qiymeti' => 25,
            'status' => 1,
        ]);

        $this->assertSame(100, $item->cemi);
    }

    /** @testdox TC_UNIT_INVOICEITEMS_SVC_002 InvoiceItemsService::updateItem recalculates cemi */
    public function test_unit_invoiceitems_svc_002_update_recalculates_cemi(): void
    {
        $existing = (new InvoiceItems)->forceFill(['sayi' => 2, 'qiymeti' => 25, 'cemi' => 50]);
        $captured = null;
        $repository = Mockery::mock(InvoiceItemsRepositoryInterface::class);
        $repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (InvoiceItems $item): bool {
                return true;
            }), Mockery::on(function (array $data) use (&$captured): bool {
                $captured = $data;

                return $data['cemi'] === 75;
            }))
            ->andReturn($existing);

        $this->app->instance(InvoiceItemsRepositoryInterface::class, $repository);

        $this->app->make(InvoiceItemsService::class)->updateItem($existing, ['sayi' => 3]);

        $this->assertIsArray($captured);
        $this->assertSame(75, $captured['cemi']);
    }

    /** @testdox TC_UNIT_FILTER_001 ApiFilter transforms the eq operator */
    public function test_unit_filter_001_api_filter_transforms_eq_operator(): void
    {
        $filter = new class extends ApiFilter
        {
            protected $safeParms = ['name' => ['eq']];

            protected $columnMap = [];
        };

        $result = $filter->transform(Request::create('/test', 'GET', ['name' => ['eq' => 'Alice']]));

        $this->assertSame([['name', '=', 'Alice']], $result);
    }

    /** @testdox TC_UNIT_FILTER_002 ApiFilter ignores unknown query parameters */
    public function test_unit_filter_002_api_filter_ignores_unknown_params(): void
    {
        $filter = new class extends ApiFilter
        {
            protected $safeParms = ['name' => ['eq']];
        };

        $result = $filter->transform(Request::create('/test', 'GET', ['unknown' => ['eq' => 'x']]));

        $this->assertSame([], $result);
    }

    /** @testdox TC_UNIT_FILTER_003 ApiFilter maps a column name and a non-eq operator */
    public function test_unit_filter_003_api_filter_maps_column_and_operator(): void
    {
        $filter = new class extends ApiFilter
        {
            protected $safeParms = ['fullName' => ['gte']];

            protected $columnMap = ['fullName' => 'name'];
        };

        $result = $filter->transform(Request::create('/test', 'GET', ['fullName' => ['gte' => 'B']]));

        $this->assertSame([['name', '>=', 'B']], $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
