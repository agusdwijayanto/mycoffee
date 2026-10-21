<div class="card-menu {{ $menu->habis ? 'habis' : '' }} {{ !empty($besar) ? 'besar' : '' }}">
    <div class="foto">
        @if($menu->habis)
            <span class="stiker-habis">{{ __('publik.stiker_habis') }}</span>
        @endif
        <img src="{{ $menu->fotoUrl() }}" alt="{{ $menu->nama }}" loading="lazy">
    </div>
    <div class="isi">
        <span class="kat">{{ $menu->category->nama }}</span>
        <h3>{{ $menu->nama }}</h3>
        <p>{{ $menu->deskripsi }}</p>
        <span class="price">{{ \App\Support\Format::rupiah($menu->harga) }}</span>
        <p style="margin-top:10px">
            <a class="btn btn-wa btn-kecil" target="_blank" rel="noopener"
               href="{{ \App\Support\Wa::tautan($settings['whatsapp'], 'Halo '.$settings['brand_name'].', saya mau pesan '.$menu->nama.'. Apakah masih tersedia?') }}">
                {{ __('publik.pesan_wa') }}
            </a>
        </p>
    </div>
</div>
