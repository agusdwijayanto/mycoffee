@extends('layouts.admin')

@section('title', 'Jadwal Karyawan')

@section('content')
<h1>Jadwal Karyawan</h1>
<div class="panel" style="max-width:560px">
    <h2 style="margin-top:0">Tambah Jadwal</h2>
    <form method="POST" action="{{ route('admin.schedules.store') }}">
        @csrf
        <div class="form-group">
            <label for="user_id">Karyawan</label>
            <select class="input" id="user_id" name="user_id" required>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="hari">Hari</label>
            <select class="input" id="hari" name="hari" required>
                @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $i => $namaHari)
                    <option value="{{ $i }}">{{ $namaHari }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Jam Masuk s/d Selesai</label>
            <div class="form-inline-flex">
                <input class="input" type="time" name="jam_masuk" required>
                <span>s/d</span>
                <input class="input" type="time" name="jam_selesai" required>
            </div>
        </div>
        <button class="btn" type="submit">Tambah Jadwal</button>
    </form>
</div>
<table class="tabel">
    <tr><th>Karyawan</th><th>Hari</th><th>Jam Masuk</th><th>Jam Selesai</th><th>Aksi</th></tr>
    @forelse($schedules as $s)
        <tr>
            <td>{{ $s->user->name }}</td>
            <td>{{ \App\Models\Schedule::namaHari($s->hari) }}</td>
            <td>{{ \Illuminate\Support\Str::substr($s->jam_masuk, 0, 5) }}</td>
            <td>{{ \Illuminate\Support\Str::substr($s->jam_selesai, 0, 5) }}</td>
            <td>
                <form class="form-inline" method="POST" action="{{ route('admin.schedules.destroy', $s) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="kosong">Belum ada jadwal.</td></tr>
    @endforelse
</table>
@endsection
