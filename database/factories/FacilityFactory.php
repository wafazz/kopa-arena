<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => 'Field ' . fake()->numberBetween(1, 10),
            'type' => 'football_field',
            'status' => 'active',
        ];
    }
}
