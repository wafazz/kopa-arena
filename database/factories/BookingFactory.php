<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'user_id' => User::factory(),
            'booking_date' => now()->addDays(fake()->numberBetween(1, 30))->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'pending',
            'booking_type' => 'normal',
            'payment_type' => 'cash',
            'payment_status' => 'deposit',
            'amount' => fake()->randomFloat(2, 50, 200),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_email' => fake()->safeEmail(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function match(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_type' => 'match',
        ]);
    }
}
