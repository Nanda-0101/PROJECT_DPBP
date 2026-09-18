<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Booking;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $q         = $request->get('q', '');
        $category  = $request->get('category', '');
        $type      = $request->get('type', '');
        $sort      = $request->get('sort', 'trending');
        $highlight = $request->get('highlight');

        $query = Product::active()->with(['category', 'seller']);

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('brand', 'like', "%{$q}%")
                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }
        
        if ($category) {
            $query->whereHas('category', fn($c) => $c->where('name', $category));
        }
        
        if ($type === 'sewa') $query->rentable();
        if ($type === 'jual') $query->sellable();

        switch ($sort) {
            case 'price-asc':  
                $query->orderByRaw('COALESCE(NULLIF(price_per_day,0), price_sale) asc'); 
                break;
            case 'price-desc': 
                $query->orderByRaw('COALESCE(NULLIF(price_per_day,0), price_sale) desc'); 
                break;
            case 'rating':     
                $query->orderByDesc('rating'); 
                break;
            default:           
                $query->orderByDesc('is_trending')->orderByDesc('rating');
        }

        $products   = $query->get();
        $categories = Category::orderBy('sort_order')->get();

        return view('marketplace', compact(
            'products', 'categories', 'q', 'category', 'type', 'sort', 'highlight'
        ));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'seller', 'specs']);
        return response()->json($product);
    }

    // ==== Beli produk ====
    public function buy(Request $request, Product $product)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Masuk dulu untuk membeli.');
        }

        $request->validate([
            'name'    => 'required|string|max:120',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'payment' => 'required|in:Transfer,QRIS,Cash',
        ]);

        if ($product->price_sale <= 0) {
            return back()->with('error', 'Produk ini tidak dijual.');
        }

        DB::transaction(function () use ($request, $product) {
            $booking = Booking::create([
                'code'           => Booking::generateCode('ORD'),
                'user_id'        => Auth::id(),
                'type'           => 'Pembelian',
                'start_date'     => now()->toDateString(),
                'end_date'       => now()->toDateString(),
                'duration_label' => '-',
                'payment_type'   => 'Bayar Penuh',
                'payment_method' => $request->payment,
                'guarantee'      => null,
                'total_amount'   => $product->price_sale,
                'paid_amount'    => $product->price_sale,
                'status'         => 'Menunggu Konfirmasi',
                'address'        => $request->address,
            ]);

            OrderItem::create([
                'booking_id'        => $booking->id,
                'product_id'        => $product->id,
                'product_name_snap' => $product->name,
                'price_snap'        => $product->price_sale,
                'qty'               => 1,
                'subtotal'          => $product->price_sale,
            ]);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Pembelian berhasil dibuat! Cek status di Dashboard.');
    }

    // ==== Jual barang (user listing) ====
    public function sell(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Masuk dulu untuk menjual barangmu.');
        }

        $request->validate([
            'name'       => 'required|string|max:150',
            'category'   => 'required|string|max:50',
            'price_sale' => 'nullable|numeric|min:0',
            'price_rent' => 'nullable|numeric|min:0',
            'desc'       => 'nullable|string',
            'photos'     => 'required|array|min:1',
            'photos.*'   => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $hargaJual = (float) $request->price_sale;
        $hargaSewa = (float) $request->price_rent;

        if ($hargaJual === 0.0 && $hargaSewa === 0.0) {
            return back()->with('error', 'Isi minimal salah satu harga.');
        }

        // Upload foto ke storage public
        $paths = [];
        foreach ($request->file('photos') as $file) {
            $paths[] = $file->store('products', 'public');
        }

        $category = Category::firstOrCreate(
            ['name' => $request->category],
            ['icon' => 'bi-box-seam']
        );

        Product::create([
            'seller_id'          => Auth::id(),
            'category_id'        => $category->id,
            'name'               => $request->name,
            'icon'               => 'bi-box-seam',
            'image'              => $paths[0], // Foto utama
            'description'        => $request->desc,
            'price_sale'         => $hargaJual,
            'price_per_day'      => $hargaSewa,
            'price_per_half_day' => $hargaSewa ? round($hargaSewa * 0.6) : 0,
            'stock'              => 1,
            'status'             => 'Menunggu Verifikasi',
        ]);

        return redirect()->route('marketplace')
            ->with('success', 'Barang berhasil ditayangkan di marketplace!');
    }
}