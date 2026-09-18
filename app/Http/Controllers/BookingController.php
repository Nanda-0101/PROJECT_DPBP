<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $products = Product::active()->rentable()->with('category')->get();
        $selected = $request->get('product');

        return view('booking', compact('products', 'selected'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'renter_name'    => 'required|string|max:120',
            'renter_phone'   => 'required|string|max:20',
            'renter_city'    => 'nullable|string|max:80',
            'note'           => 'nullable|string|max:255',
            'start_date'     => 'required|date|after_or_equal:today',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'duration_type'  => 'required|in:half,full',
            'payment_type'   => 'required|in:DP,Bayar Penuh',
            'payment_method' => 'required|in:Cash,Transfer,QRIS',
            'guarantee'      => 'required|string|max:50',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Hitung harga di server-side (jangan percaya client)
        $start = \Carbon\Carbon::parse($request->start_date);
        $end   = \Carbon\Carbon::parse($request->end_date);
        $isSameDay = $start->equalTo($end);

        if ($isSameDay) {
            if ($request->duration_type === 'half') {
                $duration = '12 Jam';
                $subtotal = $product->price_per_half_day ?: round($product->price_per_day * 0.6);
            } else {
                $duration = '1 Hari';
                $subtotal = $product->price_per_day;
            }
        } else {
            $days     = $start->diffInDays($end) + 1;
            $duration = $days . ' Hari';
            $subtotal = $product->price_per_day * $days;
        }

        $dp       = (int) round($subtotal * 0.3);
        $paidNow  = $request->payment_type === 'DP' ? $dp : $subtotal;

        $booking = DB::transaction(function () use (
            $request, $product, $start, $end, $duration, $subtotal, $paidNow
        ) {
            $booking = Booking::create([
                'code'           => Booking::generateCode('BKG'),
                'user_id'        => Auth::id() ?? 1, // guest fallback (produksi: wajib login)
                'type'           => 'Sewa',
                'start_date'     => $start->toDateString(),
                'end_date'       => $end->toDateString(),
                'duration_label' => $duration,
                'payment_type'   => $request->payment_type,
                'payment_method' => $request->payment_method,
                'guarantee'      => $request->guarantee,
                'total_amount'   => $subtotal,
                'paid_amount'    => $paidNow,
                'status'         => 'Menunggu Konfirmasi',
                'note'           => $request->note,
            ]);

            OrderItem::create([
                'booking_id'        => $booking->id,
                'product_id'        => $product->id,
                'product_name_snap' => $product->name,
                'price_snap'        => $product->price_per_day,
                'qty'               => 1,
                'subtotal'          => $subtotal,
            ]);

            return $booking;
        });

        return redirect()->route('dashboard')
            ->with('success', "Booking {$booking->code} berhasil dibuat! Menunggu konfirmasi mitra.");
    }
}