<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Booking;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\RecommendationService;
use App\Support\PaymentMethods;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Checkout sewa. Item berasal dari keranjang (lihat CartService); satu checkout
 * bisa berisi banyak alat berbeda dengan jumlah berbeda. Karena tiap alat bisa
 * dimiliki mitra berbeda, pesanan dipecah per pemilik (CamRent / mitra) —
 * seperti marketplace pada umumnya — agar konfirmasi mitra tetap jelas.
 */
class BookingController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function create(RecommendationService $recommendations)
    {
        $lines = $this->cart->selectedLines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang sewa masih kosong. Pilih alat dulu ya.');
        }

        return view('booking', [
            'lines'           => $lines,
            'initialItems'    => $lines->map(fn ($line) => [
                'id'         => $line['product']->id,
                'name'       => $line['product']->name,
                'category'   => $line['product']->category?->name ?? '-',
                'image'      => ImageHelper::url($line['product']->image),
                'icon'       => $line['product']->icon ?: 'bi-box-seam',
                'price_day'  => (float) $line['product']->price_per_day,
                'price_half' => (float) $line['product']->price_per_half_day,
                'qty'        => $line['qty'],
            ])->values(),
            'recommendations' => $recommendations->forCart(6),
            'paymentGroups'   => PaymentMethods::grouped(),
            'paymentMethods'  => collect(PaymentMethods::all())->map(fn ($m) => [
                'label'          => $m['label'],
                'type'           => $m['type'],
                'icon'           => $m['icon'],
                'account_name'   => $m['account_name'],
                'account_number' => $m['account_number'],
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'selected'       => 'required|array|min:1',
            'selected.*'     => 'integer',
            'renter_name'    => 'required|string|max:120',
            'renter_phone'   => 'required|string|max:20',
            'renter_city'    => 'nullable|string|max:80',
            'note'           => 'nullable|string|max:255',
            'start_date'     => 'required|date|after_or_equal:today',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'duration_type'  => 'required|in:half,full',
            'payment_type'   => 'required|in:DP,Bayar Penuh',
            'payment_method' => ['required', Rule::in(PaymentMethods::keys())],
            'guarantee'      => 'required|string|max:50',
        ], [
            'selected.required' => 'Pilih minimal satu alat untuk disewa.',
        ]);

        // Jumlah selalu diambil dari keranjang di server, bukan dari form.
        $cartQty = $this->cart->raw();
        $ids = collect($request->input('selected'))->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
        foreach ($ids as $id) {
            if (!isset($cartQty[$id])) {
                return redirect()->route('cart.index')
                    ->with('error', 'Isi keranjang berubah. Silakan periksa kembali sebelum checkout.');
            }
        }

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        $result = DB::transaction(function () use ($request, $ids, $cartQty, $start, $end) {
            // Kunci produk berurutan berdasarkan id supaya aman dari deadlock.
            $products = Product::query()
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $rows = [];
            foreach ($ids as $id) {
                $product = $products->get($id);
                $qty = $cartQty[$id];

                if (!$product || $product->status !== 'Aktif' || $product->price_per_day <= 0) {
                    return ['error' => 'Salah satu alat sudah tidak tersedia untuk disewa.'];
                }
                if ($product->stock < $qty || !$this->hasRentalAvailability($product, $start, $end, $qty)) {
                    return ['error' => "{$product->name} ({$qty} unit) tidak tersedia pada tanggal yang dipilih."];
                }

                [$duration, $unitPrice] = $this->priceFor($product, $start, $end, $request->duration_type);
                $rows[] = [
                    'product'  => $product,
                    'qty'      => $qty,
                    'duration' => $duration,
                    'subtotal' => $unitPrice * $qty,
                ];
            }

            $userId = Auth::id();
            if (!$userId) {
                $userId = User::createGuest($request->renter_name, $request->renter_phone)->id;
            }

            $bookings = [];
            $grouped = collect($rows)->groupBy(fn ($row) => (int) $row['product']->seller_id);

            foreach ($grouped as $groupRows) {
                $total   = $groupRows->sum('subtotal');
                $paidNow = $request->payment_type === 'DP' ? (int) round($total * 0.3) : $total;

                $booking = Booking::create([
                    'code'           => Booking::generateCode('BKG'),
                    'user_id'        => $userId,
                    'type'           => 'Sewa',
                    'start_date'     => $start->toDateString(),
                    'end_date'       => $end->toDateString(),
                    'duration_label' => $groupRows->first()['duration'],
                    'payment_type'   => $request->payment_type,
                    'payment_method' => $request->payment_method,
                    'guarantee'      => $request->guarantee,
                    'total_amount'   => $total,
                    'paid_amount'    => $paidNow,
                    'status'         => 'Menunggu Konfirmasi',
                    'note'           => $request->note,
                    'address'        => $request->renter_city,
                ]);

                foreach ($groupRows as $row) {
                    OrderItem::create([
                        'booking_id'        => $booking->id,
                        'product_id'        => $row['product']->id,
                        'product_name_snap' => $row['product']->name,
                        'price_snap'        => $row['product']->price_per_day,
                        'qty'               => $row['qty'],
                        'subtotal'          => $row['subtotal'],
                    ]);
                }

                $bookings[] = $booking;
            }

            return ['bookings' => $bookings];
        });

        if (isset($result['error'])) {
            return back()->withInput()->with('error', $result['error']);
        }

        /** @var Booking[] $bookings */
        $bookings = $result['bookings'];
        $this->cart->removeMany($ids);

        if (!Auth::check()) {
            $guestUserIds = session('guest_user_ids', []);
            $guestUserIds[] = $bookings[0]->user_id;
            session(['guest_user_ids' => array_values(array_unique($guestUserIds))]);
        }

        $codes = collect($bookings)->pluck('code');
        session()->flash('payment_summary', [
            'codes'    => $codes->all(),
            'method'   => $request->payment_method,
            'type'     => $request->payment_type,
            'total'    => (float) collect($bookings)->sum('total_amount'),
            'pay_now'  => (float) collect($bookings)->sum('paid_amount'),
            'phone'    => $request->renter_phone,
        ]);

        $label = $codes->count() > 1
            ? 'Booking '.$codes->implode(', ').' berhasil dibuat!'
            : "Booking {$codes->first()} berhasil dibuat!";

        return redirect()->route('dashboard')
            ->with('success', $label.' Menunggu konfirmasi mitra.');
    }

    /** @return array{0: string, 1: int|float} [label durasi, harga per unit untuk durasi itu] */
    private function priceFor(Product $product, Carbon $start, Carbon $end, string $durationType): array
    {
        if ($start->equalTo($end)) {
            if ($durationType === 'half') {
                return ['12 Jam', $product->price_per_half_day ?: round($product->price_per_day * 0.6)];
            }

            return ['1 Hari', $product->price_per_day];
        }

        $days = $start->diffInDays($end) + 1;

        return [$days.' Hari', $product->price_per_day * $days];
    }

    /** Cek unit yang masih bebas pada rentang tanggal, termasuk jumlah yang diminta. */
    private function hasRentalAvailability(Product $product, Carbon $start, Carbon $end, int $qty): bool
    {
        if ($qty > $product->stock) {
            return false;
        }

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
            if ($reserved + $qty > $product->stock) {
                return false;
            }
        }

        return true;
    }
}
