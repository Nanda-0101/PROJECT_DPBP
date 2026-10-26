<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Booking;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $q         = $request->get('q', '');
        $category  = $request->get('category', '');
        $sort      = $request->get('sort', 'trending');
        $source    = in_array($request->get('source'), ['mitra', 'camrent'], true)
            ? $request->get('source')
            : '';
        $highlight = $request->get('highlight');

        $query = Product::active()->with(['category', 'seller']);

        if ($source === 'mitra') {
            $query->whereHas('seller', fn($seller) => $seller->where('role', 'mitra'));
        } elseif ($source === 'camrent') {
            $query->whereNull('seller_id');
        }

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
            'products', 'categories', 'q', 'category', 'sort', 'source', 'highlight'
        ));
    }

    public function show(Product $product)
    {
        $product = Product::active()
            ->with(['category:id,name', 'seller:id,name', 'specs:id,product_id,spec_text'])
            ->findOrFail($product->id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'brand' => $product->brand,
            'image' => \App\Helpers\ImageHelper::url($product->image),
            'description' => $product->description,
            'price_per_half_day' => $product->price_per_half_day,
            'price_per_day' => $product->price_per_day,
            'price_sale' => $product->price_sale,
            'stock' => $product->stock,
            'rating' => $product->rating,
            'category' => $product->category?->name,
            'seller' => $product->seller?->name ?? 'CamRent (Vendor)',
            'specs' => $product->specs->pluck('spec_text'),
        ]);
    }

    // ==== Beli produk ====
    public function buy(Request $request, Product $product)
    {
        $request->validate([
            'name'    => 'required|string|max:120',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'payment' => 'required|in:Transfer,QRIS,Cash',
        ]);

        if (Auth::id() && (int) Auth::id() === (int) $product->seller_id) {
            return back()->with('error', 'Kamu tidak dapat membeli produk milik sendiri.');
        }

        $booking = DB::transaction(function () use ($request, $product) {
            $product = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->status !== 'Aktif' || $product->price_sale <= 0) {
                return null;
            }

            if ($product->stock < 1) {
                return null;
            }

            $userId = Auth::id();
            if (!$userId) {
                $userId = User::createGuest($request->name, $request->phone)->id;
            }

            $booking = Booking::create([
                'code'           => Booking::generateCode('ORD'),
                'user_id'        => $userId,
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

            $product->decrement('stock');
            if ((int) $product->stock === 0) {
                $product->update(['status' => 'Habis']);
            }

            return $booking;
        });

        if (!$booking) {
            return back()->with('error', 'Stok produk ini sudah habis atau tidak lagi tersedia.');
        }

        if (!Auth::check()) {
            $guestUserIds = session('guest_user_ids', []);
            $guestUserIds[] = $booking->user_id;
            session(['guest_user_ids' => array_values(array_unique($guestUserIds))]);
        }

        $destination = Auth::check() ? 'dashboard' : 'marketplace';

        return redirect()->route($destination)
            ->with('success', "Pembelian {$booking->code} berhasil dibuat!");
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