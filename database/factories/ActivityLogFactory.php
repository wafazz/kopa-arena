<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['login', 'logout', 'store', 'update', 'approve', 'reject', 'cancel']),
            'model' => 'Booking',
            'model_id' => fake()->numberBetween(1, 100),
            'details' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
