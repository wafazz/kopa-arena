@php $siteLogoWhite = \App\Models\Setting::get('logo_white', 'images/icon/logo-white.png'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Kopa Arena - Book football fields across Malaysia. Quick, easy, and hassle-free pitch booking.">
    <title>@yield('title', 'Kopa Arena - Book Your Pitch')</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1a8754">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192x192.png') }}">

    <link href="{{ asset('vendor/bootstrap-5.3.8.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome-7.1.0/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    :root {
        --ka-primary: #1a8754;
        --ka-primary-dark: #146c43;
        --ka-primary-light: #d1e7dd;
        --ka-dark: #0d1b2a;
        --ka-dark-2: #1b2838;
        --ka-accent: #e2b93b;
        --ka-light: #f8f9fa;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Poppins', sans-serif; color: #333; overflow-x: hidden; }

    /* NAVBAR */
    .navbar-ka { background: transparent; padding: 15px 0; transition: all 0.4s ease; position: fixed; width: 100%; z-index: 1000; }
    .navbar-ka.scrolled { background: rgba(13, 27, 42, 0.97); backdrop-filter: blur(10px); padding: 10px 0; box-shadow: 0 2px 30px rgba(0,0,0,0.3); }
    .navbar-ka .navbar-brand img { height: 42px; }
    .navbar-ka .nav-link { color: rgba(255,255,255,0.85); font-weight: 500; font-size: 0.95rem; padding: 8px 16px !important; transition: color 0.3s; }
    .navbar-ka .nav-link:hover, .navbar-ka .nav-link.active { color: #fff; }
    .navbar-ka .navbar-toggler { border-color: rgba(255,255,255,0.3); }
    .navbar-ka .navbar-toggler-icon { filter: invert(1); }
    .btn-nav-login { border: 2px solid var(--ka-primary); color: #fff !important; border-radius: 25px; padding: 7px 24px !important; font-weight: 600; transition: all 0.3s; }
    .btn-nav-login:hover { background: var(--ka-primary); color: #fff !important; }

    /* HERO */
    .hero-section {
        min-height: 100vh;
        background: linear-gradient(135deg, var(--ka-dark) 0%, #14352a 40%, #1b4332 60%, var(--ka-dark) 100%);
        display: flex; align-items: center;
        position: relative; overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at 70% 50%, rgba(26,135,84,0.15) 0%, transparent 50%),
                    radial-gradient(circle at 20% 80%, rgba(226,185,59,0.08) 0%, transparent 40%);
    }
    .hero-section::after {
        content: '';
        position: absolute; bottom: -2px; left: 0; right: 0; height: 100px;
        background: linear-gradient(to top, #fff 0%, transparent 100%);
    }
    .hero-content { position: relative; z-index: 2; }
    .hero-badge { display: inline-block; background: rgba(26,135,84,0.2); border: 1px solid rgba(26,135,84,0.4); color: var(--ka-primary); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600; margin-bottom: 20px; letter-spacing: 1px; }
    .hero-title { font-size: 3.8rem; font-weight: 800; color: #fff; line-height: 1.15; margin-bottom: 20px; }
    .hero-title span { background: linear-gradient(135deg, var(--ka-primary), #2dd881); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .hero-subtitle { font-size: 1.15rem; color: rgba(255,255,255,0.65); max-width: 550px; line-height: 1.7; margin-bottom: 35px; }
    .btn-hero { background: var(--ka-primary); color: #fff; border: none; border-radius: 30px; padding: 14px 38px; font-weight: 600; font-size: 1.05rem; transition: all 0.3s; text-decoration: none; display: inline-block; }
    .btn-hero:hover { background: var(--ka-primary-dark); color: #fff; transform: translateY(-3px); box-shadow: 0 10px 30px rgba(26,135,84,0.4); }
    .btn-hero-outline { background: transparent; border: 2px solid rgba(255,255,255,0.3); color: #fff; border-radius: 30px; padding: 12px 32px; font-weight: 600; font-size: 1.05rem; transition: all 0.3s; text-decoration: none; display: inline-block; margin-left: 12px; }
    .btn-hero-outline:hover { border-color: #fff; color: #fff; background: rgba(255,255,255,0.1); }

    /* SEARCH WIDGET */
    .search-widget {
        background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        padding: 30px 35px; margin-top: -60px; position: relative; z-index: 10;
    }
    .search-widget h4 { font-weight: 700; color: var(--ka-dark); margin-bottom: 20px; }
    .search-widget h4 i { color: var(--ka-primary); margin-right: 8px; }
    .btn-find { background: var(--ka-primary); color: #fff; border: none; border-radius: 10px; padding: 12px 30px; font-weight: 600; width: 100%; height: 100%; min-height: 48px; transition: all 0.3s; }
    .btn-find:hover { background: var(--ka-primary-dark); color: #fff; }

    /* SECTION */
    .section-padding { padding: 90px 0; }
    .section-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 8px; }
    .section-title span { color: var(--ka-primary); }
    .section-subtitle { color: #6c757d; font-size: 1.05rem; margin-bottom: 45px; }
    .bg-light-section { background: var(--ka-light); }

    /* BOOKING FORM */
    .booking-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; }
    .booking-header { background: linear-gradient(135deg, var(--ka-dark) 0%, #1b4332 100%); color: #fff; padding: 22px 28px; }
    .booking-header h4 { margin: 0; font-weight: 700; font-size: 1.2rem; }
    .booking-body { padding: 28px; }
    .form-label { font-weight: 600; font-size: 0.9rem; color: #495057; }
    .form-control, .form-select { border-radius: 10px; padding: 10px 14px; border: 1.5px solid #dee2e6; font-size: 0.95rem; }
    .form-control:focus, .form-select:focus { border-color: var(--ka-primary); box-shadow: 0 0 0 0.2rem rgba(26,135,84,0.15); }
    .btn-submit-booking { background: var(--ka-primary); border: none; border-radius: 12px; padding: 14px 30px; font-weight: 700; color: #fff; width: 100%; font-size: 1.05rem; transition: all 0.3s; }
    .btn-submit-booking:hover { background: var(--ka-primary-dark); color: #fff; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(26,135,84,0.3); }
    .section-divider { border: none; border-top: 1.5px solid #e9ecef; margin: 25px 0; }

    /* INFO CARD */
    .info-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); padding: 25px; }
    .info-card h5 { font-weight: 700; margin-bottom: 20px; font-size: 1.1rem; }
    .info-item { display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
    .info-item:last-child { border-bottom: none; }
    .info-icon { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 14px; flex-shrink: 0; }
    .info-label { font-size: 0.8rem; color: #6c757d; margin-bottom: 1px; }
    .info-value { font-weight: 700; font-size: 0.95rem; }
    .tips-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); padding: 25px; margin-top: 20px; }
    .tips-card h5 { font-weight: 700; margin-bottom: 15px; font-size: 1.1rem; }
    .tip-item { font-size: 0.88rem; margin-bottom: 10px; color: #555; }
    .tip-item:last-child { margin-bottom: 0; }
    .tip-item i { width: 20px; margin-right: 8px; }

    /* FEATURE CARDS */
    .feature-card { background: #fff; border-radius: 16px; padding: 35px 25px; text-align: center; transition: all 0.3s ease; box-shadow: 0 5px 25px rgba(0,0,0,0.06); height: 100%; border: 1px solid #f0f0f0; }
    .feature-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.12); border-color: transparent; }
    .feature-icon { width: 75px; height: 75px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.6rem; }
    .feature-card h5 { font-weight: 700; margin-bottom: 10px; font-size: 1.05rem; }
    .feature-card p { color: #6c757d; font-size: 0.9rem; margin-bottom: 0; line-height: 1.6; }

    /* STATS */
    .stats-section { background: linear-gradient(135deg, var(--ka-dark) 0%, #1b4332 60%, var(--ka-dark) 100%); position: relative; }
    .stat-item { text-align: center; padding: 30px 15px; }
    .stat-number { font-size: 3rem; font-weight: 800; background: linear-gradient(135deg, var(--ka-primary), #2dd881); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .stat-label { color: rgba(255,255,255,0.7); font-weight: 500; font-size: 0.95rem; margin-top: 5px; }

    /* FOOTER */
    .footer-section { background: var(--ka-dark); color: rgba(255,255,255,0.7); padding: 60px 0 0; }
    .footer-section h5 { color: #fff; font-weight: 700; margin-bottom: 20px; font-size: 1.05rem; }
    .footer-section p { font-size: 0.9rem; line-height: 1.7; }
    .footer-link { display: block; color: rgba(255,255,255,0.6); text-decoration: none; padding: 5px 0; font-size: 0.9rem; transition: all 0.3s; }
    .footer-link:hover { color: var(--ka-primary); padding-left: 5px; }
    .footer-contact-item { display: flex; align-items: flex-start; margin-bottom: 15px; font-size: 0.9rem; }
    .footer-contact-item i { color: var(--ka-primary); margin-right: 12px; margin-top: 4px; width: 16px; }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); padding: 20px 0; margin-top: 40px; }
    .footer-bottom p { margin: 0; font-size: 0.85rem; }

    /* ANIMATIONS */
    .fade-up { opacity: 0; transform: translateY(30px); transition: all 0.6s ease; }
    .fade-up.visible { opacity: 1; transform: translateY(0); }

    /* RESPONSIVE */
    @media (max-width: 991px) {
        .hero-title { font-size: 2.8rem; }
        .search-widget { margin-top: -40px; padding: 25px 20px; }
        .btn-hero-outline { margin-left: 0; margin-top: 10px; }
    }
    @media (max-width: 767px) {
        .hero-section { min-height: auto; padding: 120px 0 100px; }
        .hero-title { font-size: 2.2rem; }
        .hero-subtitle { font-size: 1rem; }
        .section-padding { padding: 60px 0; }
        .section-title { font-size: 1.7rem; }
        .stat-number { font-size: 2.2rem; }
    }
    </style>
    @stack('styles')
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-ka" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset($siteLogoWhite) }}" alt="Kopa Arena">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#book">Book Now</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Why Us</a></li>
                    {{-- ECOMMERCE ADDON
                    <li class="nav-item"><a class="nav-link" href="{{ route('shop.index') }}">Shop</a></li>
                    ECOMMERCE ADDON --}}
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    {{-- ECOMMERCE ADDON
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('shop.cart') }}">
                            <i class="fas fa-shopping-cart"></i>
                            @php $cartCount = array_sum(array_column(session('cart.items', []), 'quantity')); @endphp
                            <span class="badge bg-danger rounded-pill position-absolute" id="cartBadge" style="top:0;right:0;font-size:0.65rem;{{ $cartCount ? '' : 'display:none;' }}">{{ $cartCount }}</span>
                        </a>
                    </li>
                    ECOMMERCE ADDON --}}
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-nav-login" href="{{ route('login') }}">
                            <i class="fas fa-user me-1"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- FOOTER -->
    <footer class="footer-section" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <img src="{{ asset($siteLogoWhite) }}" alt="Kopa Arena" style="height:40px;" class="mb-3">
                    <p>Your go-to platform for booking football fields across Malaysia. Quick, easy, and hassle-free pitch booking for everyone.</p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Quick Links</h5>
                    <a href="#home" class="footer-link">Home</a>
                    <a href="#about" class="footer-link">About Us</a>
                    <a href="#book" class="footer-link">Book Now</a>
                    <a href="#features" class="footer-link">Why Choose Us</a>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Our Services</h5>
                    <a href="#book" class="footer-link">Field Booking</a>
                    <a href="#book" class="footer-link">Match Booking</a>
                    <a href="#features" class="footer-link">Corporate Events</a>
                    <a href="#features" class="footer-link">Tournaments</a>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5>Contact Us</h5>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>info@kopaarena.com</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+60 12-345 6789</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Daily: 8:00 AM - 11:00 PM</span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p>&copy; {{ date('Y') }} Kopa Arena. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('vendor/bootstrap-5.3.8.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        var nav = document.getElementById('mainNav');
        if (window.scrollY > 50) { nav.classList.add('scrolled'); }
        else { nav.classList.remove('scrolled'); }
    });

    // Smooth scroll for nav links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                var offset = 70;
                var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
                // Close mobile menu
                var collapse = document.querySelector('.navbar-collapse.show');
                if (collapse) { new bootstrap.Collapse(collapse).hide(); }
            }
        });
    });

    // Fade-up animation on scroll
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) { entry.target.classList.add('visible'); }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(function(el) { observer.observe(el); });

    // Flash messages via SweetAlert
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Booking Submitted!',
        html: '{!! session("success") !!}',
        confirmButtonColor: '#1a8754'
    });
    @endif
    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Oops!',
        html: '{!! session("error") !!}',
        confirmButtonColor: '#dc3545'
    });
    @endif
    </script>
    @stack('scripts')
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }
    </script>
</body>
</html>
