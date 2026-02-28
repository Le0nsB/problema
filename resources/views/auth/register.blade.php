@extends('layouts.app')

@section('title', 'Reģistrācija')

@section('styles')
<style>
    .auth-container {
        max-width: 400px;
        margin: 2rem auto;
        background: white;
        padding: 2.5rem;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .auth-container h2 {
        margin-bottom: 1.5rem;
        color: #1f2937;
        font-size: 1.75rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #333;
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 0.875rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-group input:focus {
        outline: none;
        border-color: #DC143C;
        box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
    }

    .error-message {
        color: #e74c3c;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .btn {
        width: 100%;
        padding: 0.875rem;
        background: #111827;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(220, 20, 60, 0.3);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 20, 60, 0.4);
    }

    .btn:active {
        transform: translateY(0);
    }

    .auth-link {
        text-align: center;
        margin-top: 1.5rem;
        color: #666;
    }

    .auth-link a {
        color: #DC143C;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
    }

    .auth-link a:hover {
        color: #1a1a1a;
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="auth-container">
    <h2>Reģistrācija</h2>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">Pilns vārds</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="username">Lietotājvārds</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required>
            @error('username')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Parole</label>
            <input type="password" id="password" name="password" required>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Apstiprini paroli</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn">Reģistrēties</button>

        <div class="auth-link">
            Jau ir konts? <a href="{{ route('login') }}">Piesakies šeit</a>
        </div>
    </form>
</div>
@endsection
