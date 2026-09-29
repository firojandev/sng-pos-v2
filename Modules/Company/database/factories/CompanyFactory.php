<?php

namespace Modules\Company\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Company\Models\Company;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'phone' => fake()->numerify('017########'),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'fiscal_year_start_month' => 7,
            'currency' => 'BDT',
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
