<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        
        $status=$this->faker->randomElement(['P','B','V']);

        return [
            'customer_id'=>Customer::factory(),
            'invoice_id'=>strtoupper($this->faker->regexify('[A-Z0-9]{8}')),
            'status'=>$status,//Billed, Paid, Void
            'invoice_date'=>$this->faker->dateTimeThisDecade(),
            'invoice_due_date'=>$this->faker->dateTimeThisDecade('+60 days'),
            'subTotal'=>$this->faker->randomFloat(),
            'percentage'=>$this->faker->randomNumber(2, false),
            'percentageAmount'=>$this->faker->randomFloat(),
            'totalAfterpercentage'=>$this->faker->randomFloat(),
            'amountPaid'=>$this->faker->randomFloat(),
            'amountDue'=>$this->faker->randomFloat(),
        ];
    }
}
