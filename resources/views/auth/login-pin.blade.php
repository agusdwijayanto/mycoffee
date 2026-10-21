<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login PIN — {{ $settings['brand_name'] }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h1>{{ $settings['brand_name'] }}</h1>
        <p style="color:var(--redup)">Login cepat kasir dengan PIN 4 angka.</p>
        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('login.pin.proses') }}">
            @csrf
            <div class="form-group">
                <label for="user_id">Pengguna</label>
                <select class="input" id="user_id" name="user_id" required>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="pin">PIN</label>
                <input class="input" id="pin" type="password" name="pin" inputmode="numeric" maxlength="4" required autocomplete="off" placeholder="••••">
            </div>
            <button class="btn btn-aksen" type="submit" style="width:100%">Masuk dengan PIN</button>
        </form>
        <p style="text-align:center; margin-top:14px">
            <a href="{{ route('login') }}">Login dengan email &amp; password</a><br>
            <a href="{{ route('home') }}">Kembali ke situs</a>
        </p>
    </div>
</div>
</body>
</html>
