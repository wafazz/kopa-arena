<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariation;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('branch', 'category', 'variations')
            ->active()
            ->whereHas('branch', fn($q) => $q->where('status', 'active'));

        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        if ($request->branch) {
            $query->where('branch_id', $request->branch);
        }
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->get();
        $categories = ProductCategory::where('status', 'active')->orderBy('sort_order')->get();
        $branches = Branch::where('status', 'active')->get();

        return view('shop.index', compact('products', 'categories', 'branches'));
    }

    public function show($slug)
    {
        $product = Product::with('branch', 'category', 'images', 'variations')
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        $related = Product::active()
            ->where('id', '!=', $product->id)
            ->where('branch_id', $product->branch_id)
            ->limit(4)
            ->get();

        return view('shop.show', compact('product', 'related'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $variation = $request->variation_id ? ProductVariation::findOrFail($request->variation_id) : null;

        // Same-branch check
        $cart = session('cart.items', []);
        if (!empty($cart)) {
            $firstItem = reset($cart);
            if ($firstItem['branch_id'] != $product->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'All cart items must be from the same branch. Please clear your cart first.',
                ], 422);
            }
        }

        $key = $product->id . '-' . ($variation ? $variation->id : '0');
        $price = $variation ? $variation->price : $product->price;
        $maxStock = $variation ? $variation->stock : $product->stock;

        $currentQty = isset($cart[$key]) ? $cart[$key]['quantity'] : 0;
        $newQty = $currentQty + $request->quantity;

        if ($newQty > $maxStock) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available.',
            ], 422);
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'variation_id' => $variation ? $variation->id : null,
            'name' => $product->name,
            'variation_name' => $variation ? $variation->name : null,
            'price' => $price,
            'quantity' => $newQty,
            'image' => $product->image,
            'branch_id' => $product->branch_id,
            'branch_name' => $product->branch->name ?? '',
            'max_stock' => $maxStock,
        ];

        session(['cart.items' => $cart]);

        return response()->json([
            'success' => true,
            'message' => 'Added to cart!',
            'cart_count' => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    public function cart()
    {
        $cart = session('cart.items', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return view('shop.cart', compact('cart', 'subtotal'));
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session('cart.items', []);
        if (isset($cart[$request->key])) {
            if ($request->quantity > $cart[$request->key]['max_stock']) {
                return response()->json(['success' => false, 'message' => 'Not enough stock.'], 422);
            }
            $cart[$request->key]['quantity'] = $request->quantity;
            session(['cart.items' => $cart]);
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return response()->json([
            'success' => true,
            'cart_count' => array_sum(array_column($cart, 'quantity')),
            'subtotal' => number_format($subtotal, 2),
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $request->validate(['key' => 'required|string']);

        $cart = session('cart.items', []);
        unset($cart[$request->key]);
        session(['cart.items' => $cart]);

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return response()->json([
            'success' => true,
            'cart_count' => array_sum(array_column($cart, 'quantity')),
            'subtotal' => number_format($subtotal, 2),
        ]);
    }

    public function checkout()
    {
        $cart = session('cart.items', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $firstItem = reset($cart);
        $branch = Branch::find($firstItem['branch_id']);

        $config = $this->getSenangPayConfig();
        $senangpayEnabled = !empty($config['merchant_id']) && !empty($config['secret_key']);

        return view('shop.checkout', compact('cart', 'subtotal', 'branch', 'senangpayEnabled'));
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'delivery_method' => 'required|in:pickup,shipping',
            'shipping_address' => 'required_if:delivery_method,shipping|nullable|string',
            'shipping_city' => 'required_if:delivery_method,shipping|nullable|string',
            'shipping_state' => 'required_if:delivery_method,shipping|nullable|string',
            'shipping_postcode' => 'required_if:delivery_method,shipping|nullable|string|max:10',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,online',
        ]);

        $cart = session('cart.items', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        $firstItem = reset($cart);
        $branchId = $firstItem['branch_id'];

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shippingFee = 0;
        $total = $subtotal + $shippingFee;

        $paymentMethod = $request->input('payment_method', 'cash');
        $config = $this->getSenangPayConfig();
        $isOnline = $paymentMethod === 'online' && !empty($config['merchant_id']) && !empty($config['secret_key']);

        $order = null;

        try {
            DB::transaction(function () use ($request, $cart, $branchId, $subtotal, $shippingFee, $total, $isOnline, &$order) {
                // Stock check + decrement with lock
                foreach ($cart as $key => $item) {
                    if ($item['variation_id']) {
                        $variation = ProductVariation::lockForUpdate()->find($item['variation_id']);
                        if (!$variation || $variation->stock < $item['quantity']) {
                            throw new \Exception("Not enough stock for {$item['name']} - {$item['variation_name']}.");
                        }
                        $variation->decrement('stock', $item['quantity']);
                    } else {
                        $product = Product::lockForUpdate()->find($item['product_id']);
                        if (!$product || $product->stock < $item['quantity']) {
                            throw new \Exception("Not enough stock for {$item['name']}.");
                        }
                        $product->decrement('stock', $item['quantity']);
                    }
                }

                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'branch_id' => $branchId,
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'delivery_method' => $request->delivery_method,
                    'shipping_address' => $request->shipping_address,
                    'shipping_city' => $request->shipping_city,
                    'shipping_state' => $request->shipping_state,
                    'shipping_postcode' => $request->shipping_postcode,
                    'notes' => $request->notes,
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'total_amount' => $total,
                    'payment_type' => $isOnline ? 'online' : 'cash',
                    'payment_status' => 'unpaid',
                    'status' => 'pending',
                ]);

                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_variation_id' => $item['variation_id'],
                        'product_name' => $item['name'],
                        'variation_name' => $item['variation_name'],
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        session()->forget('cart');

        if ($isOnline && $order) {
            return $this->redirectToSenangPay($order, $config);
        }

        return redirect()->route('shop.order.success', $order)->with('success', 'Order placed successfully!');
    }

    public function paymentReturn(Request $request)
    {
        $statusId = $request->query('status_id');
        $orderId = $request->query('order_id');
        $transactionId = $request->query('transaction_id');
        $msg = $request->query('msg');
        $hash = $request->query('hash');

        $config = $this->getSenangPayConfig();
        $secretKey = $config['secret_key'];

        $expectedHash = hash_hmac('SHA256', $secretKey . $statusId . $orderId . $transactionId . $msg, $secretKey);

        if ($hash !== $expectedHash) {
            return redirect()->route('shop.index')->with('error', 'Invalid payment response.');
        }

        $dbOrderId = str_replace('SHOP-', '', $orderId);
        $order = Order::find($dbOrderId);

        if (!$order) {
            return redirect()->route('shop.index')->with('error', 'Order not found.');
        }

        if ($statusId == 1) {
            $order->update([
                'payment_status' => 'paid',
                'payment_type' => 'online',
                'paid_at' => now(),
                'transaction_id' => $transactionId,
                'status' => 'paid',
            ]);
            return redirect()->route('shop.order.success', $order)->with('success', 'Payment successful!');
        }

        return redirect()->route('shop.order.success', $order)->with('info', 'Payment was not completed. You can pay at the branch.');
    }

    public function paymentCallback(Request $request)
    {
        $statusId = $request->input('status_id');
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');
        $msg = $request->input('msg');
        $hash = $request->input('hash');

        $config = $this->getSenangPayConfig();
        $secretKey = $config['secret_key'];

        $expectedHash = hash_hmac('SHA256', $secretKey . $statusId . $orderId . $transactionId . $msg, $secretKey);

        if ($hash !== $expectedHash) {
            return response('FAIL', 400);
        }

        $dbOrderId = str_replace('SHOP-', '', $orderId);
        $order = Order::find($dbOrderId);

        if (!$order) {
            return response('NOT FOUND', 404);
        }

        if ($statusId == 1) {
            $order->update([
                'payment_status' => 'paid',
                'payment_type' => 'online',
                'paid_at' => now(),
                'transaction_id' => $transactionId,
                'status' => 'paid',
            ]);
        }

        return response('OK');
    }

    public function orderSuccess(Order $order)
    {
        $order->load('items', 'branch');
        return view('shop.success', compact('order'));
    }

    private function getSenangPayConfig()
    {
        $mode = Setting::get('senangpay_mode', 'sandbox');
        $prefix = 'senangpay_' . $mode . '_';
        return [
            'mode' => $mode,
            'merchant_id' => Setting::get($prefix . 'merchant_id'),
            'secret_key' => Setting::get($prefix . 'secret_key'),
            'base_url' => $mode === 'production'
                ? 'https://app.senangpay.my/payment/'
                : 'https://sandbox.senangpay.my/payment/',
        ];
    }

    private function redirectToSenangPay($order, $config)
    {
        $orderId = 'SHOP-' . $order->id;
        $detail = 'Order_' . $order->order_number;
        $amount = number_format($order->total_amount, 2, '.', '');

        $hash = hash_hmac('SHA256', $config['secret_key'] . $detail . $amount . $orderId, $config['secret_key']);

        $url = $config['base_url'] . $config['merchant_id'];
        $params = http_build_query([
            'detail' => $detail,
            'amount' => $amount,
            'order_id' => $orderId,
            'hash' => $hash,
            'name' => $order->customer_name,
            'email' => $order->customer_email ?? '',
            'phone' => $order->customer_phone,
        ]);

        return redirect($url . '?' . $params);
    }
}
