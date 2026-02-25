@php $siteLogo = \App\Models\Setting::get('logo', 'images/icon/logo.png'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kopa Arena')</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1a8754">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192x192.png') }}">

    <link href="{{ asset('css/font-face.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('vendor/fontawesome-7.1.0/css/all.min.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('vendor/mdi-font/css/material-design-iconic-font.min.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('vendor/bootstrap-5.3.8.min.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('vendor/css-hamburgers/hamburgers.min.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('vendor/perfect-scrollbar/perfect-scrollbar-1.5.6.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet" media="all">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9/dist/style.min.css" rel="stylesheet">
    <style>
    .datatable-wrapper .datatable-top,
    .datatable-wrapper .datatable-bottom { padding: 10px 0; }
    .datatable-wrapper .datatable-top .datatable-search input {
        background: #1f2940; color: #fff; border: 1px solid #3a4a6b; border-radius: 6px; padding: 6px 12px;
    }
    .datatable-wrapper .datatable-top .datatable-dropdown select {
        background: #1f2940; color: #fff; border: 1px solid #3a4a6b; border-radius: 6px; padding: 6px 8px;
    }
    .datatable-wrapper .datatable-top .datatable-dropdown label { color: #8e9ab5; }
    .datatable-wrapper .datatable-bottom .datatable-info { color: #8e9ab5; }
    .datatable-wrapper .datatable-bottom .datatable-pagination .datatable-pagination-list-item-link {
        background: #1f2940; color: #8e9ab5; border: 1px solid #3a4a6b; border-radius: 4px; padding: 4px 10px; margin: 0 2px;
    }
    .datatable-wrapper .datatable-bottom .datatable-pagination .datatable-pagination-list-item.datatable-active .datatable-pagination-list-item-link {
        background: #3168f2; color: #fff; border-color: #3168f2;
    }
    .datatable-wrapper .datatable-sorter::before,
    .datatable-wrapper .datatable-sorter::after { border-bottom-color: #8e9ab5; border-top-color: #8e9ab5; }
    .datatable-wrapper .datatable-empty { color: #8e9ab5; text-align: center; padding: 20px 0; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
        <header class="header-mobile d-block d-lg-none">
            <div class="header-mobile__bar">
                <div class="container-fluid">
                    <div class="header-mobile-inner">
                        <a class="logo" href="{{ route('dashboard') }}">
                            <img src="{{ asset($siteLogo) }}" alt="Kopa Arena" />
                        </a>
                        <button class="hamburger hamburger--slider" type="button">
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <nav class="navbar-mobile">
                <div class="container-fluid">
                    <ul class="navbar-mobile__list list-unstyled">
                        @if(auth()->user()->hasRole(['superadmin', 'hq_staff']))
                        <li>
                            <a href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        @if(auth()->user()->hasPermission('manage_branches'))
                        <li>
                            <a href="{{ route('branches.index') }}">
                                <i class="fas fa-building"></i>Branches</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_facilities'))
                        <li>
                            <a href="{{ route('facilities.index') }}">
                                <i class="fas fa-futbol"></i>Facilities</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_pricing_rules'))
                        <li>
                            <a href="{{ route('pricing-rules.index') }}">
                                <i class="fas fa-tags"></i>Pricing Rules</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_bookings'))
                        <li>
                            <a href="{{ route('bookings.index') }}">
                                <i class="fas fa-calendar-check"></i>Bookings</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_checkins'))
                        <li>
                            <a href="{{ route('checkins.scan') }}">
                                <i class="fas fa-qrcode"></i>Check-In</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_close_sales'))
                        <li>
                            <a href="{{ route('close-sales.index') }}">
                                <i class="fas fa-cash-register"></i>Close Sales</a>
                        </li>
                        @endif
                        {{-- ECOMMERCE ADDON
                        @if(auth()->user()->hasPermission('manage_products') || auth()->user()->hasPermission('manage_orders'))
                        <li class="has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-store"></i>E-Commerce
                                <span class="arrow"><i class="fas fa-angle-down"></i></span>
                            </a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                @if(auth()->user()->hasPermission('manage_products'))
                                <li><a href="{{ route('products.index') }}"><i class="fas fa-box-open"></i>Products</a></li>
                                <li><a href="{{ route('product-categories.index') }}"><i class="fas fa-layer-group"></i>Categories</a></li>
                                @endif
                                @if(auth()->user()->hasPermission('manage_orders'))
                                <li><a href="{{ route('orders.index') }}"><i class="fas fa-shopping-cart"></i>Orders</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        ECOMMERCE ADDON --}}
                        @if(auth()->user()->hasPermission('manage_staff'))
                        <li>
                            <a href="{{ route('staff.index') }}">
                                <i class="fas fa-users"></i>Staff</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view_reports'))
                        <li>
                            <a href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-bar"></i>Reports</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view_activity_logs'))
                        <li>
                            <a href="{{ route('activity-logs.index') }}">
                                <i class="fas fa-clipboard-list"></i>Activity Logs</a>
                        </li>
                        @endif
                        @if(auth()->user()->role === 'superadmin')
                        <li>
                            <a href="{{ route('settings.index') }}">
                                <i class="fas fa-cog"></i>Settings</a>
                        </li>
                        @endif
                        @endif
                        @if(auth()->user()->isBranchStaff())
                        <li>
                            <a href="{{ route('branch.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        @if(auth()->user()->hasPermission('manage_facilities'))
                        <li>
                            <a href="{{ route('branch.facilities.index') }}">
                                <i class="fas fa-futbol"></i>Facilities</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_bookings'))
                        <li>
                            <a href="{{ route('branch.bookings.index') }}">
                                <i class="fas fa-calendar-check"></i>Bookings</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_checkins'))
                        <li>
                            <a href="{{ route('branch.checkins.scan') }}">
                                <i class="fas fa-qrcode"></i>Check-In</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_close_sales'))
                        <li>
                            <a href="{{ route('branch.close-sales.index') }}">
                                <i class="fas fa-cash-register"></i>Close Sales</a>
                        </li>
                        @endif
                        {{-- ECOMMERCE ADDON
                        @if(auth()->user()->hasPermission('manage_products') || auth()->user()->hasPermission('manage_orders'))
                        <li class="has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-store"></i>E-Commerce
                                <span class="arrow"><i class="fas fa-angle-down"></i></span>
                            </a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                @if(auth()->user()->hasPermission('manage_products'))
                                <li><a href="{{ route('branch.products.index') }}"><i class="fas fa-box-open"></i>Products</a></li>
                                @endif
                                @if(auth()->user()->hasPermission('manage_orders'))
                                <li><a href="{{ route('branch.orders.index') }}"><i class="fas fa-shopping-cart"></i>Orders</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        ECOMMERCE ADDON --}}
                        @if(auth()->user()->hasPermission('manage_staff'))
                        <li>
                            <a href="{{ route('branch.staff.index') }}">
                                <i class="fas fa-users"></i>Staff</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view_reports'))
                        <li>
                            <a href="{{ route('branch.reports.index') }}">
                                <i class="fas fa-chart-bar"></i>Reports</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view_activity_logs'))
                        <li>
                            <a href="{{ route('branch.activity-logs.index') }}">
                                <i class="fas fa-clipboard-list"></i>Activity Logs</a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </nav>
        </header>
        <!-- END HEADER MOBILE-->

        <!-- MENU SIDEBAR-->
        <aside class="menu-sidebar d-none d-lg-block">
            <div class="logo">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset($siteLogo) }}" alt="Kopa Arena" />
                </a>
            </div>
            <div class="menu-sidebar__content js-scrollbar1">
                <nav class="navbar-sidebar">
                    <ul class="list-unstyled navbar__list">
                        @if(auth()->user()->hasRole(['superadmin', 'hq_staff']))
                        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        @if(auth()->user()->hasPermission('manage_branches'))
                        <li class="{{ request()->routeIs('branches.*') ? 'active' : '' }}">
                            <a href="{{ route('branches.index') }}">
                                <i class="fas fa-building"></i>Branches</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_facilities'))
                        <li class="{{ request()->routeIs('facilities.*') ? 'active' : '' }}">
                            <a href="{{ route('facilities.index') }}">
                                <i class="fas fa-futbol"></i>Facilities</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_pricing_rules'))
                        <li class="{{ request()->routeIs('pricing-rules.*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-rules.index') }}">
                                <i class="fas fa-tags"></i>Pricing Rules</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_bookings'))
                        <li class="{{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                            <a href="{{ route('bookings.index') }}">
                                <i class="fas fa-calendar-check"></i>Bookings</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_checkins'))
                        <li class="{{ request()->routeIs('checkins.*') ? 'active' : '' }}">
                            <a href="{{ route('checkins.scan') }}">
                                <i class="fas fa-qrcode"></i>Check-In</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_close_sales'))
                        <li class="{{ request()->routeIs('close-sales.*') ? 'active' : '' }}">
                            <a href="{{ route('close-sales.index') }}">
                                <i class="fas fa-cash-register"></i>Close Sales</a>
                        </li>
                        @endif
                        {{-- ECOMMERCE ADDON
                        @if(auth()->user()->hasPermission('manage_products') || auth()->user()->hasPermission('manage_orders'))
                        @php $ecomActive = request()->routeIs('products.*') || request()->routeIs('product-categories.*') || request()->routeIs('orders.*'); @endphp
                        <li class="has-sub {{ $ecomActive ? 'active open' : '' }}">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-store"></i>E-Commerce
                                <span class="arrow {{ $ecomActive ? 'up' : '' }}"><i class="fas fa-angle-down"></i></span>
                            </a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list" {!! $ecomActive ? 'style="display:block;"' : '' !!}>
                                @if(auth()->user()->hasPermission('manage_products'))
                                <li class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                                    <a href="{{ route('products.index') }}"><i class="fas fa-box-open"></i>Products</a>
                                </li>
                                <li class="{{ request()->routeIs('product-categories.*') ? 'active' : '' }}">
                                    <a href="{{ route('product-categories.index') }}"><i class="fas fa-layer-group"></i>Categories</a>
                                </li>
                                @endif
                                @if(auth()->user()->hasPermission('manage_orders'))
                                <li class="{{ request()->routeIs('orders.*') ? 'active' : '' }}">
                                    <a href="{{ route('orders.index') }}"><i class="fas fa-shopping-cart"></i>Orders</a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        ECOMMERCE ADDON --}}
                        @if(auth()->user()->hasPermission('manage_staff'))
                        <li class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">
                            <a href="{{ route('staff.index') }}">
                                <i class="fas fa-users"></i>Staff</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view_reports'))
                        <li class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <a href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-bar"></i>Reports</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view_activity_logs'))
                        <li class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                            <a href="{{ route('activity-logs.index') }}">
                                <i class="fas fa-clipboard-list"></i>Activity Logs</a>
                        </li>
                        @endif
                        @if(auth()->user()->role === 'superadmin')
                        <li class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <a href="{{ route('settings.index') }}">
                                <i class="fas fa-cog"></i>Settings</a>
                        </li>
                        @endif
                        @endif
                        @if(auth()->user()->isBranchStaff())
                        <li class="{{ request()->routeIs('branch.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('branch.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        @if(auth()->user()->hasPermission('manage_facilities'))
                        <li class="{{ request()->routeIs('branch.facilities.*') ? 'active' : '' }}">
                            <a href="{{ route('branch.facilities.index') }}">
                                <i class="fas fa-futbol"></i>Facilities</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_bookings'))
                        <li class="{{ request()->routeIs('branch.bookings.*') ? 'active' : '' }}">
                            <a href="{{ route('branch.bookings.index') }}">
                                <i class="fas fa-calendar-check"></i>Bookings</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_checkins'))
                        <li class="{{ request()->routeIs('branch.checkins.*') ? 'active' : '' }}">
                            <a href="{{ route('branch.checkins.scan') }}">
                                <i class="fas fa-qrcode"></i>Check-In</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_close_sales'))
                        <li class="{{ request()->routeIs('branch.close-sales.*') ? 'active' : '' }}">
                            <a href="{{ route('branch.close-sales.index') }}">
                                <i class="fas fa-cash-register"></i>Close Sales</a>
                        </li>
                        @endif
                        {{-- ECOMMERCE ADDON
                        @if(auth()->user()->hasPermission('manage_products') || auth()->user()->hasPermission('manage_orders'))
                        @php $branchEcomActive = request()->routeIs('branch.products.*') || request()->routeIs('branch.orders.*'); @endphp
                        <li class="has-sub {{ $branchEcomActive ? 'active open' : '' }}">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-store"></i>E-Commerce
                                <span class="arrow {{ $branchEcomActive ? 'up' : '' }}"><i class="fas fa-angle-down"></i></span>
                            </a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list" {!! $branchEcomActive ? 'style="display:block;"' : '' !!}>
                                @if(auth()->user()->hasPermission('manage_products'))
                                <li class="{{ request()->routeIs('branch.products.*') ? 'active' : '' }}">
                                    <a href="{{ route('branch.products.index') }}"><i class="fas fa-box-open"></i>Products</a>
                                </li>
                                @endif
                                @if(auth()->user()->hasPermission('manage_orders'))
                                <li class="{{ request()->routeIs('branch.orders.*') ? 'active' : '' }}">
                                    <a href="{{ route('branch.orders.index') }}"><i class="fas fa-shopping-cart"></i>Orders</a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        ECOMMERCE ADDON --}}
                        @if(auth()->user()->hasPermission('manage_staff'))
                        <li class="{{ request()->routeIs('branch.staff.*') ? 'active' : '' }}">
                            <a href="{{ route('branch.staff.index') }}">
                                <i class="fas fa-users"></i>Staff</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view_reports'))
                        <li class="{{ request()->routeIs('branch.reports.*') ? 'active' : '' }}">
                            <a href="{{ route('branch.reports.index') }}">
                                <i class="fas fa-chart-bar"></i>Reports</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view_activity_logs'))
                        <li class="{{ request()->routeIs('branch.activity-logs.*') ? 'active' : '' }}">
                            <a href="{{ route('branch.activity-logs.index') }}">
                                <i class="fas fa-clipboard-list"></i>Activity Logs</a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>
        <!-- END MENU SIDEBAR-->

        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <!-- HEADER DESKTOP-->
            <header class="header-desktop">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="header-wrap">
                            <div class="form-header">
                                <span class="text-muted">@yield('title', 'Kopa Arena')</span>
                            </div>
                            <div class="header-button">
                                <div class="account-wrap">
                                    <div class="account-item clearfix js-item-menu">
                                        <div class="image" style="width:36px;height:36px;border-radius:50%;overflow:hidden;background:#1f2940;display:flex;align-items:center;justify-content:center;">
                                            @if(auth()->user()->profile_image)
                                                <img src="{{ asset(auth()->user()->profile_image) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                            @else
                                                <i class="fas fa-user" style="color:#8e9ab5;font-size:16px;"></i>
                                            @endif
                                        </div>
                                        <div class="content">
                                            <a class="js-acc-btn" href="#">{{ auth()->user()->name }}</a>
                                        </div>
                                        <div class="account-dropdown js-dropdown">
                                            <div class="info clearfix">
                                                <div class="image" style="width:50px;height:50px;border-radius:50%;overflow:hidden;background:#1f2940;display:flex;align-items:center;justify-content:center;">
                                                    @if(auth()->user()->profile_image)
                                                        <img src="{{ asset(auth()->user()->profile_image) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                                    @else
                                                        <i class="fas fa-user" style="color:#8e9ab5;font-size:22px;"></i>
                                                    @endif
                                                </div>
                                                <div class="content">
                                                    <h5 class="name">
                                                        <a href="#">{{ auth()->user()->name }}</a>
                                                    </h5>
                                                    <span class="email">{{ auth()->user()->email }}</span>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__body">
                                                <div class="account-dropdown__item">
                                                    <a href="{{ route('profile.show') }}">
                                                        <i class="zmdi zmdi-account"></i>Profile</a>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__footer">
                                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                                    @csrf
                                                </form>
                                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                    <i class="zmdi zmdi-power"></i>Logout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- END HEADER DESKTOP-->

            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @yield('content')

                        <div class="row">
                            <div class="col-md-12">
                                <div class="copyright">
                                    <p>Kopa Arena &copy; {{ date('Y') }}. All rights reserved.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
        </div>
        <!-- END PAGE CONTAINER-->

    </div>

    <script src="{{ asset('js/vanilla-utils.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap-5.3.8.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/perfect-scrollbar/perfect-scrollbar-1.5.6.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap5-init.js') }}"></script>
    <script src="{{ asset('js/main-vanilla.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.querySelectorAll('[data-confirm-delete]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) this.submit();
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof simpleDatatables === 'undefined') return;
        document.querySelectorAll('[data-datatable]').forEach(function(table) {
            var cols = table.querySelectorAll('thead th');
            var colCount = cols.length;
            var columns = [{ select: 0, type: 'number' }];
            if (colCount > 1) {
                columns.push({ select: colCount - 1, sortable: false });
            }
            new simpleDatatables.DataTable(table, {
                searchable: true,
                sortable: true,
                perPage: 25,
                perPageSelect: [10, 25, 50, 100],
                columns: columns,
                labels: {
                    placeholder: 'Search...',
                    perPage: 'per page',
                    noRows: 'No entries found',
                    noResults: 'No results match your search',
                    info: 'Showing {start} to {end} of {rows} entries'
                }
            });
        });
    });
    </script>
    @stack('scripts')
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }
    </script>
</body>
</html>
