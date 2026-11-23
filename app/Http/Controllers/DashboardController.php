<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookingsQuery = Booking::with(['items.product'])
            ->orderByDesc('created_at');
        if ($user) {
            $bookingsQuery->forUser($user->id);
        } else {
            $bookingsQuery->whereIn('user_id', session('guest_user_ids', []));
        }
        $bookings = $bookingsQuery->get();

        $isMitra = $user?->isMitra() ?? false;
        $canMonitorOrders = in_array($user?->role, ['mitra', 'admin'], true);

        $monitoredOrders = collect();
        $myProducts = collect();
        if ($canMonitorOrders) {
            $monitoredOrders = Booking::with(['items.product', 'user'])
                ->orderByDesc('created_at')
                ->get();
        }

        if ($isMitra) {
            $myProducts = Product::with('category')
                ->where('seller_id', $user->id)
                ->get();
        }

        return view('dashboard', compact(
            'user', 'bookings', 'isMitra', 'canMonitorOrders', 'monitoredOrders', 'myProducts'
        ));
    }

    public function updateOrderStatus(Booking $booking, string $status)
    {
        $user = Auth::user();
        $ownsProduct = $user->isMitra()
            && $booking->items()
                ->whereHas('product', fn ($query) => $query->where('seller_id', $user->id))
                ->exists();

        if (!$ownsProduct) {
            abort(403);
        }

        $updated = DB::transaction(function () use ($booking, $status) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $transitions = [
                'Menunggu Konfirmasi' => ['Dikonfirmasi', 'Ditolak'],
                'Dikonfirmasi' => ['Selesai'],
            ];

            if (!in_array($status, $transitions[$booking->status] ?? [], true)) {
                return false;
            }

            $booking->update(['status' => $status]);
            return true;
        });

        if (!$updated) {
            return back()->with('error', 'Status tidak valid.');
        }

        return back()->with('success', "Status pesanan {$booking->code} diperbarui.");
    }

    public function bookingDetail(Booking $booking)
    {
        $user = Auth::user();
        $canView = (int) $booking->user_id === (int) $user->id
            || ($user->isMitra() && $booking->items()
                ->whereHas('product', fn ($query) => $query->where('seller_id', $user->id))
                ->exists());

        if (!$canView) {
            abort(403);
        }

        $booking->load(['items.product', 'user']);
        return response()->json($booking);
    }
}