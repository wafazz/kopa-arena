<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CloseSale;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private function branchId()
    {
        return auth()->user()->branch_id;
    }

    public function index()
    {
        $orders = Order::where('branch_id', $this->branchId())->latest()->get();
        return view('branch.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->branch_id !== $this->branchId()) {
            abort(403);
        }
        $order->load('items', 'branch');
        return view('branch.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->branch_id !== $this->branchId()) {
            abort(403);
        }

        if (CloseSale::isClosed($order->branch_id, $order->created_at->toDateString())) {
            return back()->with('error', 'Cannot update order. Sales for this date have been closed.');
        }

        $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,completed,cancelled',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product_variation_id) {
                    ProductVariation::where('id', $item->product_variation_id)->increment('stock', $item->quantity);
                } elseif ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }
        }

        $data = ['status' => $newStatus];
        if ($request->tracking_number) {
            $data['tracking_number'] = $request->tracking_number;
        }
        if ($newStatus === 'paid' && !$order->paid_at) {
            $data['payment_status'] = 'paid';
            $data['paid_at'] = now();
        }

        $order->update($data);

        ActivityLog::log('updateStatus', 'Order', $order->id, 'Status: ' . $oldStatus . ' → ' . $newStatus);
        return back()->with('success', 'Order status updated to ' . ucfirst($newStatus) . '.');
    }
}
