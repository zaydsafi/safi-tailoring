<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private Cart $cart)
    {
    }

    public function index()
    {
        $cartItems = $this->cart->content();
        $subtotal = $this->cart->subtotal();

        return view('shop.cart', compact('cartItems', 'subtotal'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:100'],
            'size' => ['nullable', 'string', 'max:20'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        $qty = $data['qty'] ?? 1;

        if ($product->type === 'ready' && !$product->inStock($qty)) {
            return back()->with('error', t('flash.only', 'Sorry, only') . ' ' . $product->stock . ' ' . t('flash.left_in_stock', 'left in stock for this item.'));
        }

        if ($product->type === 'ready' && !empty($product->sizes) && empty($data['size'])) {
            return back()->with('error', t('flash.select_size', 'Please select a size first.'));
        }

        $this->cart->add($product->id, $qty, $data['size'] ?? null);

        return redirect()->route('cart.index')->with('success', $product->name . ' ' . t('flash.added_to_cart', 'added to your cart.'));
    }

    public function update(Request $request, string $key)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $this->cart->update($key, $data['qty']);

        return back()->with('success', t('flash.cart_updated', 'Cart updated.'));
    }

    public function remove(string $key)
    {
        $this->cart->remove($key);

        return back()->with('success', t('flash.item_removed', 'Item removed from cart.'));
    }
}
