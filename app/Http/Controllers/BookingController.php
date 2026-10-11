<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'product_id'     => [
                'required',
                Rule::exists('products', 'id')
                    ->where(fn ($query) => $query
                        ->where('status', 'Aktif')
                        ->where('price_per_day', '>', 0)),
            ],
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

        // Hitung harga di server-side (jangan percaya client)
        $start = \Carbon\Carbon::parse($request->start_date);
        $end   = \Carbon\Carbon::parse($request->end_date);
        $isSameDay = $start->equalTo($end);

        $booking = DB::transaction(function () use ($request, $start, $end, $isSameDay) {
            $product = Product::query()
                ->whereKey($request->product_id)
                ->where('status', 'Aktif')
                ->where('price_per_day', '>', 0)
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->stock < 1 || !$this->hasRentalAvailability($product, $start, $end)) {
                return null;
            }

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

            $userId = Auth::id();
            if (!$userId) {
                $userId = User::createGuest(
                    $request->renter_name,
                    $request->renter_phone
                )->id;
            }

            $booking = Booking::create([
                'code'           => Booking::generateCode('BKG'),
                'user_id'        => $userId,
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
                'address'        => $request->renter_city,
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

        if (!$booking) {
            return back()->with('error', 'Alat tersebut tidak tersedia pada tanggal yang dipilih.');
        }

        if (!Auth::check()) {
            $guestUserIds = session('guest_user_ids', []);
            $guestUserIds[] = $booking->user_id;
            session(['guest_user_ids' => array_values(array_unique($guestUserIds))]);
        }

        $destination = Auth::check() ? 'dashboard' : 'booking';

        return redirect()->route($destination)
            ->with('success', "Booking {$booking->code} berhasil dibuat! Menunggu konfirmasi mitra.");
    }

    private function hasRentalAvailability(Product $product, \Carbon\Carbon $start, \Carbon\Carbon $end): bool
    {
        $bookings = Booking::query()
            ->where('type', 'Sewa')
            ->whereIn('status', ['Menunggu Konfirmasi', 'Dikonfirmasi'])
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->with(['items' => fn ($query) => $query->where('product_id', $product->id)])
            ->get(['id', 'start_date', 'end_date']);

        $events = [];
        foreach ($bookings as $booking) {
            $reservedStart = $booking->start_date->greaterThan($start)
                ? $booking->start_date
                : $start;
            $reservedEnd = $booking->end_date->lessThan($end)
                ? $booking->end_date
                : $end;
            $quantity = (int) $booking->items->sum('qty');
            $startKey = $reservedStart->toDateString();
            $events[$startKey] = ($events[$startKey] ?? 0) + $quantity;

            if ($reservedEnd->lessThan($end)) {
                $releaseKey = $reservedEnd->copy()->addDay()->toDateString();
                $events[$releaseKey] = ($events[$releaseKey] ?? 0) - $quantity;
            }
        }

        ksort($events);
        $reserved = 0;
        foreach ($events as $quantity) {
            $reserved += $quantity;
            if ($reserved >= $product->stock) {
                return false;
            }
        }

        return true;
    }
}