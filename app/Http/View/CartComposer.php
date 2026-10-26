<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;

class CartComposer
{
    public function compose(View $view)
    {
        // Ambil data cart dari session
        $cartItems = session('cart', []);
        $cartCount = count($cartItems);
        
        // Hitung total quantity
        $totalQty = collect($cartItems)->sum('qty');
        
        // Kirim data ke view
        $view->with('cartCount', $cartCount);
        $view->with('cartTotalQty', $totalQty);
    }
}