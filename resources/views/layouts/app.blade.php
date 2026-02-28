<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Autobusu kavējumu uzskaite')</title>
    @yield('styles')
    @if (class_exists(\Illuminate\Support\Facades\Vite::class))
        @vite(['resources/css/layout.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <nav>
        <div class="container">
            <h1><i>Omnibuss Cēsis</i></h1>
            <div class="nav-links">
                @auth
                    <span>Sveiki, {{ Auth::user()->username }}!</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit">Izrakstīties</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Pieteikties</a>
                    <a href="{{ route('register') }}">Reģistrēties</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
