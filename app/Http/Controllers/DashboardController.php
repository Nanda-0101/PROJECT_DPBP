<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookings = Booking::with(['items.product'])
            ->forUser($user->id)
            ->orderByDesc('created_at')
            ->get();

        $isMitra = $user->isMitra();

        // Data khusus mitra
        $myProducts = collect();
        $myOrders   = collect();
        if ($isMitra) {
            $myProducts = Product::with('category')
                ->where('seller_id', $user->id)
                ->get();

            $myProductIds = $myProducts->pluck('id');

            $myOrders = Booking::with(['items', 'user'])
                ->whereHas('items', fn($q) => $q->whereIn('product_id', $myProductIds))
                ->orderByDesc('created_at')
                ->get();
        }

        return view('dashboard', compact(
            'user', 'bookings', 'isMitra', 'myProducts', 'myOrders'
        ));
    }

    public function updateOrderStatus(Booking $booking, string $status)
    {
        if (!in_array($status, ['Dikonfirmasi', 'Ditolak', 'Selesai', 'Batal'])) {
            return back()->with('error', 'Status tidak valid.');
        }

        $booking->update(['status' => $status]);

        return back()->with('success', "Status pesanan {$booking->code} diperbarui.");
    }

    public function bookingDetail(Booking $booking)
    {
        $booking->load(['items.product', 'user']);
        return response()->json($booking);
    }
}