<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Product::active()->featured()->with('category')->latest()->take(8)->get();
        $categories = Category::active()->withCount(['products' => fn ($q) => $q->active()])->orderBy('sort_order')->take(6)->get();
        $newArrivals = Product::active()->where('is_featured', false)->latest()->take(4)->get();

        return view('shop.home', [
            'featured' => $featured,
            'categories' => $categories,
            'newArrivals' => $newArrivals,
            'heroTitle' => t('home.hero_title', Setting::get('hero_title')),
            'heroSubtitle' => t('home.hero_subtitle', Setting::get('hero_subtitle')),
        ]);
    }
}
