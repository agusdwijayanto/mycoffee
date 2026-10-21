@extends('layouts.admin')

@section('title', 'Pengguna & PIN')

@section('content')
<h1>Pengguna &amp; PIN Kasir</h1>
<p style="color:var(--redup)">PIN dipakai untuk login cepat di halaman Login PIN. PIN bawaan (admin 1111, kasir 1234) adalah dummy dan wajib diganti untuk pemakaian nyata.</p>
<table class="tabel">
    <tr><th>Nama</th><th>Email</th><th>Peran</th><th>PIN</th><th>Atur PIN Baru</th></tr>
    @foreach($users as $u)
        <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->role }}</td>
            <td>{{ $u->pin ? 'Sudah diatur' : 'Belum diatur' }}</td>
            <td>
                <form class="form-inline" method="POST" action="{{ route('admin.users.pin', $u) }}">
                    @csrf
                    <input class="input input-mini" type="text" name="pin" inputmode="numeric" maxlength="4" placeholder="4 angka" required>
                    <button class="btn btn-kecil" type="submit">Simpan PIN</button>
                </form>
            </td>
        </tr>
    @endforeach
</table>
@endsection
