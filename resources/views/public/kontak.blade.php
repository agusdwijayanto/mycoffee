@extends('layouts.public')

@section('title', 'Kontak')

@section('content')
<section class="section">
    <div class="container">
        <h2 class="section-title">Kontak &amp; Lokasi</h2>
        <p class="section-sub">Data kontak di halaman ini adalah data dummy untuk keperluan portofolio.</p>

        @if(session('sukses_reservasi'))
            <div class="alert alert-sukses">{{ session('sukses_reservasi') }}</div>
        @endif
        @if(session('sukses_testimoni'))
            <div class="alert alert-sukses">{{ session('sukses_testimoni') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="info-grid">
            <div class="info-box">
                <h3>Alamat</h3>
                <p>{{ $settings['alamat'] }}</p>
                <h3>Jam Buka</h3>
                <p>Setiap hari, {{ $settings['jam_buka'] }} WIB</p>
                @if(!empty($settings['jam_buka_libur']))
                    <p>Hari libur: {{ $settings['jam_buka_libur'] }}</p>
                @endif
                <p style="margin-top:12px">
                    <a class="btn btn-kecil" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($settings['alamat']) }}">Petunjuk Arah</a>
                </p>
            </div>
            <div class="info-box">
                <h3>Hubungi Kami</h3>
                <p>Telepon: {{ $settings['telepon'] }}</p>
                <p>WhatsApp: {{ $settings['whatsapp'] }}</p>
                <p>Email: {{ $settings['email'] }}</p>
                <p>Instagram: {{ $settings['instagram'] }}</p>
                <p style="margin-top:12px">
                    <a class="btn btn-wa btn-kecil" target="_blank" rel="noopener" href="{{ \App\Support\Wa::tautan($settings['whatsapp'], 'Halo '.$settings['brand_name'].', saya mau bertanya.') }}">Chat &amp; Simpan WhatsApp Kami</a>
                </p>
            </div>
            <div class="map-box">
                <span class="pin">📍</span>
                <strong>Peta Lokasi</strong>
                <p>{{ $settings['alamat'] }}<br>Lokasi dummy — gunakan tombol Petunjuk Arah untuk membuka Google Maps.</p>
                @if($hero)
                    <img src="{{ $hero->fotoUrl() }}" alt="{{ $hero->judul }}" style="border-radius:8px; margin-top:10px" loading="lazy">
                @endif
            </div>
        </div>

        <div class="form-grid">
            <div class="panel">
                <h3 style="margin-top:0">Reservasi Meja</h3>
                <form method="POST" action="{{ route('reservasi.simpan') }}">
                    @csrf
                    <div class="form-group">
                        <label for="r_nama">Nama</label>
                        <input class="input" id="r_nama" name="nama" value="{{ old('nama') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="r_wa">Nomor WhatsApp</label>
                        <input class="input" id="r_wa" name="whatsapp" value="{{ old('whatsapp') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="r_tanggal">Tanggal</label>
                        <input class="input" id="r_tanggal" type="date" name="tanggal" value="{{ old('tanggal') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="r_jam">Jam</label>
                        <input class="input" id="r_jam" type="time" name="jam" value="{{ old('jam') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="r_orang">Jumlah Orang</label>
                        <input class="input" id="r_orang" type="number" name="jumlah_orang" min="1" max="100" value="{{ old('jumlah_orang', 2) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="r_catatan">Catatan (opsional)</label>
                        <textarea class="input" id="r_catatan" name="catatan" rows="2">{{ old('catatan') }}</textarea>
                    </div>
                    <button class="btn btn-aksen" type="submit">Kirim Reservasi</button>
                </form>
            </div>

            <div class="panel">
                <h3 style="margin-top:0">Tulis Testimoni</h3>
                <p style="color:var(--redup)">Testimoni tampil di Beranda setelah disetujui admin.</p>
                <form method="POST" action="{{ route('testimoni.simpan') }}">
                    @csrf
                    <div class="form-group">
                        <label for="t_nama">Nama</label>
                        <input class="input" id="t_nama" name="nama" value="{{ old('nama') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="t_rating">Rating</label>
                        <select class="input" id="t_rating" name="rating">
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ (int) old('rating', 5) === $i ? 'selected' : '' }}>{{ $i }} bintang</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="t_isi">Testimoni</label>
                        <textarea class="input" id="t_isi" name="isi" rows="4" required>{{ old('isi') }}</textarea>
                    </div>
                    <button class="btn" type="submit">Kirim Testimoni</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
