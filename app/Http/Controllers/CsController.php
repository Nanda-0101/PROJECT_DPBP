<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $bookings = $user
            ? Booking::forUser($user->id)->get()
            : collect();

        $tickets = $user
            ? Ticket::where('user_id', $user->id)->orderByDesc('created_at')->get()
            : collect();

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
        $request->validate([
            'category'   => 'required|string|max:80',
            'booking_id' => 'nullable|exists:bookings,id',
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

        return back()->with('success', "Pengaduan {$ticket->code} terkirim. Tim CS akan menghubungi segera.");
    }
}