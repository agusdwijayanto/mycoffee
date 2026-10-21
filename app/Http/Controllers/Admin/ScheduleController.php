<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with('user')->orderBy('hari')->orderBy('jam_masuk')->get();
        $users = User::orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'hari' => ['required', 'integer', 'min:0', 'max:6'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i'],
        ]);

        Schedule::create($data);

        return back()->with('sukses', 'Jadwal berhasil ditambahkan.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return back()->with('sukses', 'Jadwal berhasil dihapus.');
    }
}
