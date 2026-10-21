<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::latest('tanggal')->paginate(15);

        return view('admin.reservations.index', compact('reservations'));
    }

    public function ubahStatus(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'status' => ['required', 'in:menunggu,dikonfirmasi,selesai,dibatalkan'],
        ]);

        $reservation->update($data);

        return back()->with('sukses', 'Status reservasi '.$reservation->nama.' diubah menjadi '.$data['status'].'.');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return back()->with('sukses', 'Reservasi berhasil dihapus.');
    }
}
