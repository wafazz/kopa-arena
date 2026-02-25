<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function createFacility(): Facility
    {
        return Facility::factory()->create();
    }

    private function bookingData(Facility $facility, array $overrides = []): array
    {
        return array_merge([
            'facility_id' => $facility->id,
            'booking_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'booking_type' => 'normal',
            'customer_name' => 'Test Customer',
            'customer_phone' => '0123456789',
            'customer_email' => 'test@example.com',
            'payment_type' => 'cash',
            'payment_status' => 'deposit',
            'notes' => '',
        ], $overrides);
    }

    public function test_superadmin_can_create_booking(): void
    {
        $user = User::factory()->superadmin()->create();
        $facility = $this->createFacility();

        $response = $this->actingAs($user)->post(route('bookings.store'), $this->bookingData($facility));

        $response->assertRedirect(route('bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'facility_id' => $facility->id,
            'customer_name' => 'Test Customer',
            'status' => 'pending',
        ]);
    }

    public function test_store_creates_activity_log(): void
    {
        $user = User::factory()->superadmin()->create();
        $facility = $this->createFacility();

        $this->actingAs($user)->post(route('bookings.store'), $this->bookingData($facility));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'store',
            'model' => 'Booking',
        ]);
    }

    public function test_double_booking_prevented(): void
    {
        $user = User::factory()->superadmin()->create();
        $facility = $this->createFacility();
        $date = now()->addDays(5)->format('Y-m-d');

        Booking::factory()->create([
            'facility_id' => $facility->id,
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->post(route('bookings.store'), $this->bookingData($facility, [
            'booking_date' => $date,
            'start_time' => '10:00',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_approve_changes_status_and_logs(): void
    {
        $user = User::factory()->superadmin()->create();
        $booking = Booking::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)->post(route('bookings.approve', $booking));

        $booking->refresh();
        $this->assertEquals('approved', $booking->status);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'approve',
            'model' => 'Booking',
            'model_id' => $booking->id,
        ]);
    }

    public function test_reject_changes_status(): void
    {
        $user = User::factory()->superadmin()->create();
        $booking = Booking::factory()->create(['status' => 'pending']);

        $this->actingAs($user)->post(route('bookings.reject', $booking));

        $booking->refresh();
        $this->assertEquals('rejected', $booking->status);
    }

    public function test_cancel_changes_status(): void
    {
        $user = User::factory()->superadmin()->create();
        $booking = Booking::factory()->create(['status' => 'approved']);

        $this->actingAs($user)->post(route('bookings.cancel', $booking));

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    public function test_rejected_slot_becomes_available(): void
    {
        $facility = $this->createFacility();
        $date = now()->addDays(5)->format('Y-m-d');

        Booking::factory()->create([
            'facility_id' => $facility->id,
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'rejected',
        ]);

        $this->assertTrue(Booking::isSlotAvailable($facility->id, $date, '10:00', '11:30'));
    }

    public function test_is_slot_available_true_for_empty(): void
    {
        $facility = $this->createFacility();
        $date = now()->addDays(10)->format('Y-m-d');

        $this->assertTrue(Booking::isSlotAvailable($facility->id, $date, '10:00', '11:30'));
    }

    public function test_is_slot_available_false_for_occupied(): void
    {
        $facility = $this->createFacility();
        $date = now()->addDays(5)->format('Y-m-d');

        Booking::factory()->create([
            'facility_id' => $facility->id,
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'approved',
        ]);

        $this->assertFalse(Booking::isSlotAvailable($facility->id, $date, '10:00', '11:30'));
    }

    public function test_is_slot_available_excludes_given_id(): void
    {
        $facility = $this->createFacility();
        $date = now()->addDays(5)->format('Y-m-d');

        $booking = Booking::factory()->create([
            'facility_id' => $facility->id,
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'approved',
        ]);

        $this->assertTrue(Booking::isSlotAvailable($facility->id, $date, '10:00', '11:30', $booking->id));
    }

    public function test_match_booking_creates_parent(): void
    {
        $user = User::factory()->superadmin()->create();
        $facility = $this->createFacility();

        $response = $this->actingAs($user)->post(route('bookings.store'), $this->bookingData($facility, [
            'booking_type' => 'match',
        ]));

        $response->assertRedirect(route('bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'facility_id' => $facility->id,
            'booking_type' => 'match',
            'match_parent_id' => null,
        ]);
    }

    public function test_match_opponent_joins_parent(): void
    {
        $user = User::factory()->superadmin()->create();
        $facility = $this->createFacility();
        $date = now()->addDays(5)->format('Y-m-d');

        $parent = Booking::factory()->match()->create([
            'facility_id' => $facility->id,
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'match_parent_id' => null,
        ]);

        $response = $this->actingAs($user)->post(route('bookings.store'), $this->bookingData($facility, [
            'booking_date' => $date,
            'start_time' => '10:00',
            'booking_type' => 'match',
            'match_parent_id' => $parent->id,
            'customer_name' => 'Opponent Team',
        ]));

        $response->assertRedirect(route('bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'match_parent_id' => $parent->id,
            'customer_name' => 'Opponent Team',
            'booking_type' => 'match',
        ]);
    }
}
