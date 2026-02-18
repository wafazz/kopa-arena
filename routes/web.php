<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PricingRuleController;
use App\Http\Controllers\CloseSaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Branch;
use Illuminate\Support\Facades\Route;

// Public routes (no auth)
Route::get('/', [PublicController::class, 'index'])->name('landing');
Route::get('/booked-slots', [PublicController::class, 'bookedSlots'])->name('public.booked-slots');
Route::post('/book', [PublicController::class, 'store'])->name('public.book');
Route::get('/payment/return', [PublicController::class, 'paymentReturn'])->name('public.payment.return');
Route::post('/payment/callback', [PublicController::class, 'paymentCallback'])->name('public.payment.callback');
Route::get('/booking/{booking}/details', [PublicController::class, 'bookingDetails'])->name('public.booking.details');
Route::post('/booking/{booking}/self-checkin', [PublicController::class, 'selfCheckin'])->name('public.booking.self-checkin');
Route::get('/checkin/verify/{checkin_token}', [PublicController::class, 'checkinVerify'])->name('public.checkin.verify');

// Shop routes (public)
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/product/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::post('/shop/cart/add', [ShopController::class, 'addToCart'])->name('shop.cart.add');
Route::get('/shop/cart', [ShopController::class, 'cart'])->name('shop.cart');
Route::post('/shop/cart/update', [ShopController::class, 'updateCart'])->name('shop.cart.update');
Route::post('/shop/cart/remove', [ShopController::class, 'removeFromCart'])->name('shop.cart.remove');
Route::get('/shop/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');
Route::post('/shop/checkout', [ShopController::class, 'processCheckout'])->name('shop.processCheckout');
Route::get('/shop/payment/return', [ShopController::class, 'paymentReturn'])->name('shop.payment.return');
Route::post('/shop/payment/callback', [ShopController::class, 'paymentCallback'])->name('shop.payment.callback');
Route::get('/shop/order/{order}/success', [ShopController::class, 'orderSuccess'])->name('shop.order.success');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/calendar-events', [DashboardController::class, 'calendarEvents'])->name('dashboard.calendar-events');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // HQ Module
    Route::middleware('role:superadmin,hq_staff')->group(function () {
        Route::middleware('permission:manage_branches')->group(function () {
            Route::resource('branches', BranchController::class);
        });
        Route::middleware('permission:manage_facilities')->group(function () {
            Route::resource('facilities', FacilityController::class);
        });
        Route::middleware('permission:manage_pricing_rules')->group(function () {
            Route::resource('pricing-rules', PricingRuleController::class)->except('show');
        });
        Route::middleware('permission:manage_bookings')->group(function () {
            Route::get('bookings/booked-slots', [BookingController::class, 'bookedSlots'])->name('bookings.booked-slots');
            Route::get('bookings/calendar-events', [BookingController::class, 'calendarEvents'])->name('bookings.calendar-events');
            Route::resource('bookings', BookingController::class);
            Route::post('bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
            Route::post('bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
            Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        });
        Route::middleware('permission:manage_close_sales')->group(function () {
            Route::get('close-sales', [CloseSaleController::class, 'index'])->name('close-sales.index');
            Route::get('close-sales/walkin', [CloseSaleController::class, 'walkinCreate'])->name('close-sales.walkin.create');
            Route::post('close-sales/walkin', [CloseSaleController::class, 'walkinStore'])->name('close-sales.walkin.store');
            Route::post('close-sales/close', [CloseSaleController::class, 'close'])->name('close-sales.close');
            Route::post('close-sales/{closeSale}/reopen', [CloseSaleController::class, 'reopen'])->name('close-sales.reopen');
            Route::post('close-sales/{booking}/mark-paid', [CloseSaleController::class, 'markPaid'])->name('close-sales.mark-paid');
            Route::post('close-sales/{order}/mark-order-paid', [CloseSaleController::class, 'markOrderPaid'])->name('close-sales.mark-order-paid');
        });
        Route::middleware('permission:manage_checkins')->group(function () {
            Route::get('checkins', [CheckInController::class, 'index'])->name('checkins.index');
            Route::get('checkins/scan', [CheckInController::class, 'scan'])->name('checkins.scan');
            Route::post('checkins/process', [CheckInController::class, 'process'])->name('checkins.process');
            Route::get('checkins/verify/{checkin_token}', [CheckInController::class, 'verify'])->name('checkins.verify');
        });
        Route::middleware('permission:manage_products')->group(function () {
            Route::resource('product-categories', ProductCategoryController::class)->except('show');
            Route::resource('products', ProductController::class);
        });
        Route::middleware('permission:manage_orders')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        });
        Route::middleware('permission:manage_staff')->group(function () {
            Route::resource('staff', StaffController::class);
        });
        Route::middleware('permission:view_reports')->group(function () {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
        });
        Route::middleware('permission:view_activity_logs')->group(function () {
            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        });
        Route::middleware('role:superadmin')->group(function () {
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });

    // Branch Module
    Route::middleware('role:branch_manager,branch_staff')->prefix('branch')->name('branch.')->group(function () {
        Route::get('/dashboard', [Branch\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/calendar-events', [Branch\DashboardController::class, 'calendarEvents'])->name('dashboard.calendar-events');
        Route::middleware('permission:manage_facilities')->group(function () {
            Route::resource('facilities', Branch\FacilityController::class)->except('show');
        });
        Route::middleware('permission:manage_bookings')->group(function () {
            Route::get('bookings/booked-slots', [Branch\BookingController::class, 'bookedSlots'])->name('bookings.booked-slots');
            Route::get('bookings/calendar-events', [Branch\BookingController::class, 'calendarEvents'])->name('bookings.calendar-events');
            Route::resource('bookings', Branch\BookingController::class)->except('show');
            Route::post('bookings/{booking}/approve', [Branch\BookingController::class, 'approve'])->name('bookings.approve');
            Route::post('bookings/{booking}/reject', [Branch\BookingController::class, 'reject'])->name('bookings.reject');
            Route::post('bookings/{booking}/cancel', [Branch\BookingController::class, 'cancel'])->name('bookings.cancel');
        });
        Route::middleware('permission:manage_close_sales')->group(function () {
            Route::get('close-sales', [Branch\CloseSaleController::class, 'index'])->name('close-sales.index');
            Route::get('close-sales/walkin', [Branch\CloseSaleController::class, 'walkinCreate'])->name('close-sales.walkin.create');
            Route::post('close-sales/walkin', [Branch\CloseSaleController::class, 'walkinStore'])->name('close-sales.walkin.store');
            Route::post('close-sales/close', [Branch\CloseSaleController::class, 'close'])->name('close-sales.close');
            Route::post('close-sales/{closeSale}/reopen', [Branch\CloseSaleController::class, 'reopen'])->name('close-sales.reopen');
            Route::post('close-sales/{booking}/mark-paid', [Branch\CloseSaleController::class, 'markPaid'])->name('close-sales.mark-paid');
            Route::post('close-sales/{order}/mark-order-paid', [Branch\CloseSaleController::class, 'markOrderPaid'])->name('close-sales.mark-order-paid');
        });
        Route::middleware('permission:manage_checkins')->group(function () {
            Route::get('checkins', [Branch\CheckInController::class, 'index'])->name('checkins.index');
            Route::get('checkins/scan', [Branch\CheckInController::class, 'scan'])->name('checkins.scan');
            Route::post('checkins/process', [Branch\CheckInController::class, 'process'])->name('checkins.process');
            Route::get('checkins/verify/{checkin_token}', [Branch\CheckInController::class, 'verify'])->name('checkins.verify');
        });
        Route::middleware('permission:manage_products')->group(function () {
            Route::resource('products', Branch\ProductController::class)->except('show');
        });
        Route::middleware('permission:manage_orders')->group(function () {
            Route::get('orders', [Branch\OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [Branch\OrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{order}/status', [Branch\OrderController::class, 'updateStatus'])->name('orders.update-status');
        });
        Route::middleware('permission:manage_staff')->group(function () {
            Route::resource('staff', Branch\StaffController::class)->except('show');
        });
        Route::middleware('permission:view_reports')->group(function () {
            Route::get('reports', [Branch\ReportController::class, 'index'])->name('reports.index');
            Route::post('reports/generate', [Branch\ReportController::class, 'generate'])->name('reports.generate');
        });
        Route::middleware('permission:view_activity_logs')->group(function () {
            Route::get('activity-logs', [Branch\ActivityLogController::class, 'index'])->name('activity-logs.index');
        });
    });
});

require __DIR__.'/auth.php';
