<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CloseSale;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('branch')->latest()->get();
        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items', 'branch');
        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        if (CloseSale::isClosed($order->branch_id, $order->created_at->toDateString())) {
            return back()->with('error', 'Cannot update order. Sales for this date have been closed.');
        }

        $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,completed,cancelled',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Restore stock on cancel
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

    public function destroy(Order $order)
    {
        if (CloseSale::isClosed($order->branch_id, $order->created_at->toDateString())) {
            return back()->with('error', 'Cannot delete order. Sales for this date have been closed.');
        }

        // Restore stock if not cancelled
        if ($order->status !== 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product_variation_id) {
                    ProductVariation::where('id', $item->product_variation_id)->increment('stock', $item->quantity);
                } elseif ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }
        }

        ActivityLog::log('destroy', 'Order', $order->id, 'Order #' . $order->id);
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }
}
