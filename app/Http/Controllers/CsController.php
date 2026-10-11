<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $guestUserIds = session('guest_user_ids', []);

        $bookings = $user
            ? Booking::forUser($user->id)->with('items')->get()
            : (count($guestUserIds)
                ? Booking::whereIn('user_id', $guestUserIds)->with('items')->get()
                : collect());

        $tickets = $user
            ? Ticket::where('user_id', $user->id)->orderByDesc('created_at')->get()
            : Ticket::whereIn('id', session('guest_ticket_ids', []))
                ->orderByDesc('created_at')
                ->get();

        $faqs = [
            ['q' => 'Bagaimana cara membatalkan booking?', 'a' => 'Buka Dashboard > Riwayat, klik "Ada masalah?".'],
            ['q' => 'Apa bedanya DP dan Bayar Penuh?',      'a' => 'DP 30% di awal, sisanya saat pengambilan.'],
            ['q' => 'Jaminan apa saja yang diterima?',      'a' => 'KTP, SIM, atau Paspor asli.'],
            ['q' => 'Metode pembayaran apa saja?',          'a' => 'Cash, Transfer bank, dan QRIS.'],
            ['q' => 'Bagaimana jika alat rusak?',           'a' => 'Laporkan via form pengaduan di halaman ini.'],
            ['q' => 'Bagaimana cara jadi mitra?',           'a' => 'Kunjungi halaman "Jadi Mitra" dan isi formulir.'],
        ];

        return view('cs', compact('user', 'bookings', 'tickets', 'faqs'));
    }

    public function store(Request $request)
    {
        $guestUserIds = session('guest_user_ids', []);
        $request->validate([
            'category'   => 'required|string|max:80',
            'booking_id' => Auth::check()
                ? ['nullable', Rule::exists('bookings', 'id')->where('user_id', Auth::id())]
                : ($guestUserIds
                    ? ['nullable', Rule::exists('bookings', 'id')->where(
                        fn ($query) => $query->whereIn('user_id', $guestUserIds)
                    )]
                    : ['nullable', 'prohibited']),
            'name'       => 'required|string|max:120',
            'contact'    => 'required|string|max:150',
            'description'=> 'required|string',
        ]);

        $ticket = Ticket::create([
            'code'        => Ticket::generateCode(),
            'user_id'     => Auth::id(),
            'booking_id'  => $request->booking_id,
            'category'    => $request->category,
            'name'        => $request->name,
            'contact'     => $request->contact,
            'description' => $request->description,
            'status'      => 'Diproses',
        ]);

        if (!Auth::check()) {
            $guestTicketIds = session('guest_ticket_ids', []);
            $guestTicketIds[] = $ticket->id;
            session(['guest_ticket_ids' => array_values(array_unique($guestTicketIds))]);
        }

        return back()->with('success', "Pengaduan {$ticket->code} terkirim. Tim CS akan menghubungi segera.");
    }
}