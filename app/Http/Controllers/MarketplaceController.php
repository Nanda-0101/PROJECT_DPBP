<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Support\ProductGallery;
use App\Support\SpecSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

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

        $query = Product::active()->rentable()->with(['category', 'seller']);

        // Spesifikasi singkat untuk chip di kartu (dilewati bila tabelnya belum ada).
        if (Schema::hasTable('product_specs')) {
            $query->with('specs:id,product_id,spec_text,sort_order');
        }

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
                $query->orderBy('price_per_day'); 
                break;
            case 'price-desc': 
                $query->orderByDesc('price_per_day'); 
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

    public function show(Product $product, CartService $cart)
    {
        $product = Product::active()->rentable()
            ->with(['category:id,name', 'seller:id,name', 'specs:id,product_id,spec_text'])
            ->findOrFail($product->id);

        // Sinyal "dilihat" untuk rekomendasi checkout.
        $cart->recordView($product);

        $sheet = SpecSheet::parse($product->specs->pluck('spec_text'));

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'brand' => $product->brand,
            'image' => \App\Helpers\ImageHelper::url($product->image),
            'description' => $product->description,
            'price_per_half_day' => $product->price_per_half_day,
            'price_per_day' => $product->price_per_day,
            'stock' => $product->stock,
            'rating' => $product->rating,
            'category' => $product->category?->name,
            'seller' => $product->seller?->name ?? 'CamRent (Vendor)',
            'specs' => $product->specs->pluck('spec_text'),
            'spec_rows' => $sheet['rows'],
            'spec_features' => $sheet['features'],
            'images' => ProductGallery::for($product),
            'cart_add_url' => route('cart.add', $product),
            'can_rent' => !Auth::user()?->isMitra()
                || (int) $product->seller_id !== (int) Auth::id(),
        ]);
    }

    // ==== Sewakan barang (mitra listing) ====
    public function rentOut(Request $request)
    {
        $user = Auth::user();
        if (!$user?->canManageCatalog()) {
            return back()
                ->with('error', 'Hanya akun admin atau mitra yang dapat menambahkan barang.');
        }

        $request->validate([
            'name'       => 'required|string|max:150',
            'category'   => 'required|string|max:50',
            'price_rent' => 'required|numeric|min:1',
            'stock'      => 'nullable|integer|min:1|max:99',
            'desc'       => 'nullable|string',
            'photos'     => 'required|array|min:1|max:'.(ProductGallery::MAX_EXTRA + 1),
            'photos.*'   => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'price_rent.required' => 'Isi harga sewa per hari.',
            'price_rent.min'      => 'Harga sewa per hari harus lebih dari 0.',
        ]);

        $hargaSewa = (float) $request->price_rent;

        // Upload foto ke storage public
        $paths = [];
        foreach ($request->file('photos') as $file) {
            $paths[] = $file->store('products', 'public');
        }

        $category = Category::firstOrCreate(
            ['name' => $request->category],
            ['icon' => 'bi-box-seam']
        );

        $product = Product::create([
            'seller_id'          => $user->isAdmin() ? null : $user->id,
            'category_id'        => $category->id,
            'name'               => $request->name,
            'icon'               => 'bi-box-seam',
            'image'              => $paths[0], // Foto utama
            'description'        => $request->desc,
            'price_sale'         => 0,
            'price_per_day'      => $hargaSewa,
            'price_per_half_day' => round($hargaSewa * 0.6),
            'stock'              => (int) ($request->stock ?: 1),
            'status'             => 'Aktif',
        ]);

        // Foto ke-2 dst. disimpan sebagai sudut tambahan di galeri specsheet.
        ProductGallery::storeExtras($product, array_slice($paths, 1));

        return redirect()->route('marketplace')
            ->with('success', 'Barang berhasil ditayangkan di marketplace dan siap disewa.');
    }

    public function remove(Request $request, Product $product)
    {
        if (Auth::user()?->role !== 'mitra') {
            return back()
                ->with('error', 'Hanya akun dengan role mitra yang dapat menghapus barang.');
        }

        if ((int) $product->seller_id !== (int) Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'stock_to_remove' => 'required|integer|min:1',
        ]);
        $stockToRemove = (int) $validated['stock_to_remove'];

        $remainingStock = DB::transaction(function () use ($product, $stockToRemove) {
            $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($stockToRemove > (int) $lockedProduct->stock) {
                throw ValidationException::withMessages([
                    'stock_to_remove' => 'Jumlah stok yang dihapus melebihi stok yang tersedia.',
                ]);
            }

            $remaining = (int) $lockedProduct->stock - $stockToRemove;
            $updates = ['stock' => $remaining];

            if ($remaining === 0) {
                $updates['status'] = 'Habis';
            }

            $lockedProduct->update($updates);

            return $remaining;
        });

        return redirect()->route('marketplace')->with(
            'success',
            "Stok sebanyak {$stockToRemove} unit berhasil dihapus. Sisa stok: {$remainingStock} unit."
        );
    }
}
