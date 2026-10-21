@extends('layouts.public')

@section('title', 'Login')

@section('content')
<section class="section">
    <div class="container">
        <div class="login-wrap">
            <p class="brand-mini">{{ $settings['brand_name'] }}</p>
            <h1>Login Panel</h1>
            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('login.proses') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="input" id="password" type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label style="font-weight:normal"><input type="checkbox" name="remember"> Ingat saya</label>
                </div>
                <button class="btn" type="submit" style="width:100%">Masuk</button>
            </form>
            <p style="font-size:13px; color:var(--redup); text-align:center; margin-top:16px">
                Akun dummy — admin@mycoffee.id / password<br>
                kasir@mycoffee.id / password<br>
                <a href="{{ route('login.pin') }}">Login cepat dengan PIN kasir</a>
            </p>
        </div>
    </div>
</section>
@endsection
