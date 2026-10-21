<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /** Tombol absen toggle: belum masuk -> catat masuk; sudah masuk belum pulang -> catat pulang. */
    public function toggle(Request $request)
    {
        $user = $request->user();
        $absen = Attendance::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        if (! $absen) {
            Attendance::create([
                'user_id' => $user->id,
                'tanggal' => today()->toDateString(),
                'jam_masuk_at' => now(),
            ]);

            return back()->with('sukses', 'Absen masuk tercatat pukul '.now()->format('H:i').'.');
        }

        if ($absen->jam_keluar_at) {
            return back()->withErrors(['absensi' => 'Absensi hari ini sudah lengkap (masuk dan pulang).']);
        }

        $absen->update(['jam_keluar_at' => now()]);

        return back()->with('sukses', 'Absen pulang tercatat pukul '.now()->format('H:i').'.');
    }
}
