<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Payroll LIM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .login-card { background: #fff; border-radius: 1rem; padding: 2.5rem 2rem; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
        .brand-icon { width: 52px; height: 52px; background: #2563eb; border-radius: .75rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .brand-icon i { color: #fff; font-size: 1.4rem; }
        .form-control { border-radius: .5rem; border: 1px solid #cbd5e1; font-size: .875rem; padding: .5rem .75rem; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); outline: none; }
        .btn-login { background: linear-gradient(135deg, #1d4ed8, #2563eb); border: none; border-radius: .5rem; color: #fff; font-weight: 600; padding: .55rem 1rem; width: 100%; font-size: .9rem; transition: opacity .15s; }
        .btn-login:hover { opacity: .9; color: #fff; }
        label { font-size: .82rem; font-weight: 500; color: #374151; margin-bottom: .35rem; }
        .invalid-feedback { font-size: .78rem; }
    </style>
</head>
<body>
<div class="login-card">
    @php
        $__appLogo    = \App\Models\AppSetting::get('app_logo');
        $__appName    = \App\Models\AppSetting::get('app_name', 'Payroll LIM');
        $__appTagline = \App\Models\AppSetting::get('app_tagline', 'Sistem Penggajian');
    @endphp
    <div class="brand-icon" @if($__appLogo) style="background:#fff;padding:4px;overflow:hidden;border:1px solid #e2e8f0" @endif>
        @if($__appLogo)
            <img src="{{ asset('storage/'.$__appLogo) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain">
        @else
            <i class="bi bi-file-earmark-text-fill"></i>
        @endif
    </div>
    <h5 class="text-center fw-700 mb-0" style="font-size:1.05rem;font-weight:700">{{ $__appName }}</h5>
    <p class="text-center text-muted mb-4" style="font-size:.78rem">{{ $__appTagline }}</p>

    @if(session('error'))
        <div class="alert alert-danger py-2" style="font-size:.82rem;border-radius:.5rem">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" autofocus autocomplete="email" placeholder="admin@example.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   autocomplete="current-password" placeholder="••••••••">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember" style="font-size:.8rem">Ingat saya</label>
            </div>
        </div>
        <button type="submit" class="btn btn-login"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</button>
    </form>
</div>
</body>
</html>
