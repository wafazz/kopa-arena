@php $siteLogoWhite = \App\Models\Setting::get('logo_white', 'images/icon/logo-white.png'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Kopa Arena</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1a8754">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192x192.png') }}">
    <link href="{{ asset('vendor/bootstrap-5.3.8.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome-7.1.0/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0d1b2a 0%, #14352a 40%, #1b4332 60%, #0d1b2a 100%);
        position: relative;
        overflow: hidden;
    }
    body::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background:
            radial-gradient(circle at 20% 80%, rgba(26,135,84,0.12) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(26,135,84,0.08) 0%, transparent 50%);
        pointer-events: none;
    }
    body::after {
        content: '';
        position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231a8754' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none; opacity: 0.5;
    }

    .login-card {
        position: relative; z-index: 1;
        width: 100%; max-width: 420px;
        background: rgba(255,255,255,0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 20px;
        padding: 40px 35px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.3);
    }

    .login-logo { text-align: center; margin-bottom: 30px; }
    .login-logo img { height: 55px; }
    .login-logo h4 { color: rgba(255,255,255,0.6); font-size: 0.85rem; font-weight: 400; margin-top: 10px; letter-spacing: 1px; }

    .form-label { color: rgba(255,255,255,0.7); font-size: 0.85rem; font-weight: 500; margin-bottom: 6px; }

    .form-control-custom {
        width: 100%;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 12px;
        padding: 12px 16px 12px 44px;
        color: #fff;
        font-size: 0.95rem;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
        outline: none;
    }
    .form-control-custom::placeholder { color: rgba(255,255,255,0.3); }
    .form-control-custom:focus {
        border-color: #1a8754;
        background: rgba(26,135,84,0.08);
        box-shadow: 0 0 0 3px rgba(26,135,84,0.15);
    }

    .input-group-custom {
        position: relative;
    }
    .input-icon {
        position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
        color: rgba(255,255,255,0.35); font-size: 0.95rem; z-index: 2;
        transition: color 0.3s;
    }
    .input-group-custom:focus-within .input-icon { color: #1a8754; }

    .toggle-password {
        position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
        background: none; border: none; color: rgba(255,255,255,0.35);
        cursor: pointer; font-size: 0.95rem; z-index: 2; padding: 0;
        transition: color 0.3s;
    }
    .toggle-password:hover { color: rgba(255,255,255,0.7); }

    .form-check-input {
        background-color: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.2);
    }
    .form-check-input:checked { background-color: #1a8754; border-color: #1a8754; }
    .form-check-label { color: rgba(255,255,255,0.6); font-size: 0.85rem; }

    .btn-login {
        width: 100%;
        background: linear-gradient(135deg, #1a8754 0%, #146c43 100%);
        border: none;
        border-radius: 12px;
        padding: 13px;
        color: #fff;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(26,135,84,0.35);
        background: linear-gradient(135deg, #1e9d62 0%, #1a8754 100%);
    }
    .btn-login:active { transform: translateY(0); }

    .forgot-link {
        color: rgba(255,255,255,0.5);
        font-size: 0.82rem;
        text-decoration: none;
        transition: color 0.3s;
    }
    .forgot-link:hover { color: #1a8754; }

    .landing-link {
        text-align: center; margin-top: 24px;
    }
    .landing-link a {
        color: rgba(255,255,255,0.45);
        font-size: 0.82rem;
        text-decoration: none;
        transition: color 0.3s;
    }
    .landing-link a:hover { color: #1a8754; }

    .alert-danger {
        background: rgba(220,53,69,0.12);
        border: 1px solid rgba(220,53,69,0.25);
        color: #f8a4ad;
        border-radius: 10px;
        font-size: 0.85rem;
        padding: 10px 15px;
    }
    .alert-info {
        background: rgba(26,135,84,0.12);
        border: 1px solid rgba(26,135,84,0.25);
        color: #7dd3a8;
        border-radius: 10px;
        font-size: 0.85rem;
        padding: 10px 15px;
    }

    /* Floating orbs */
    .orb {
        position: absolute; border-radius: 50%;
        background: radial-gradient(circle, rgba(26,135,84,0.08), transparent);
        pointer-events: none;
        animation: float 8s ease-in-out infinite;
    }
    .orb-1 { width: 300px; height: 300px; top: -100px; right: -80px; animation-delay: 0s; }
    .orb-2 { width: 200px; height: 200px; bottom: -60px; left: -60px; animation-delay: 3s; }
    .orb-3 { width: 150px; height: 150px; top: 50%; left: 10%; animation-delay: 5s; }

    @keyframes float {
        0%, 100% { transform: translate(0, 0); }
        33% { transform: translate(15px, -20px); }
        66% { transform: translate(-10px, 15px); }
    }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="login-card">
        <div class="login-logo">
            <a href="{{ route('landing') }}">
                <img src="{{ asset($siteLogoWhite) }}" alt="Kopa Arena">
            </a>
            <h4>STAFF PORTAL</h4>
        </div>

        @if(session('status'))
        <div class="alert alert-info mb-3">{{ session('status') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger mb-3">
            @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group-custom">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control-custom" placeholder="Enter your email" value="{{ old('email') }}" required autofocus autocomplete="username">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control-custom" placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>

        <div class="landing-link">
            <a href="{{ route('landing') }}"><i class="fas fa-arrow-left me-1"></i> Back to website</a>
        </div>
    </div>

    <script>
    function togglePassword() {
        var input = document.getElementById('password');
        var icon = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }
    </script>
</body>
</html>
