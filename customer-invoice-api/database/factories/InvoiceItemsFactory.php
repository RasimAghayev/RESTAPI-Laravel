<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Invoice;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItems>
 */
class InvoiceItemsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::inRandomOrder()->first()->invoice_id,
            'item_id' => $this->faker->numberBetween(1, 20),
            'vin' => $this->faker->word(),
            'brend' => $this->faker->word(),
            'marka' => $this->faker->word(),
            'sayi' => $this->faker->numberBetween(0, 5),
            'qiymeti' => $this->faker->randomNumber(6, true),
            'cemi' => $this->faker->randomNumber(6, true),
            'status' => $this->faker->numberBetween(0, 5),
        ];
    }
}