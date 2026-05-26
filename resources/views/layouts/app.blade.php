<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LIM Management') — Sistem Manajemen Penggajian</title>

    {{-- Local assets compiled by Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg-from: #1e3a8a;
            --sidebar-bg-to:   #1d4ed8;
            --primary: #2563eb;
        }
        body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; }

        /* ── Sidebar ── */
        #sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--sidebar-bg-from) 0%, var(--sidebar-bg-to) 100%);
            display: flex; flex-direction: column;
            z-index: 1040; overflow-y: auto;
            box-shadow: 4px 0 24px rgba(0,0,0,.18);
            transition: transform .25s ease;
        }
        #sidebar .brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.12);
            display: flex; align-items: center; gap: .75rem;
        }
        #sidebar .brand-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: #fff; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.15); flex-shrink: 0;
        }
        #sidebar .brand-icon i { color: var(--primary); font-size: 1.1rem; }
        #sidebar .brand-title { font-size: .95rem; font-weight: 700; color: #fff; line-height: 1.2; }
        #sidebar .brand-sub   { font-size: .7rem; color: rgba(147,197,253,.9); }

        #sidebar .nav-section {
            font-size: .65rem; font-weight: 700; color: rgba(147,197,253,.7);
            text-transform: uppercase; letter-spacing: .08em;
            padding: 1.25rem 1.25rem .35rem;
        }
        #sidebar .nav-item a {
            display: flex; align-items: center; gap: .75rem;
            padding: .6rem 1.25rem; border-radius: .6rem;
            margin: .1rem .5rem;
            font-size: .845rem; font-weight: 500;
            color: rgba(191,219,254,.9);
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        #sidebar .nav-item a i { font-size: 1rem; flex-shrink: 0; }
        #sidebar .nav-item a:hover { background: rgba(255,255,255,.12); color: #fff; }
        #sidebar .nav-item a.active { background: #fff; color: var(--primary); font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        #sidebar .nav-item a.active i { color: var(--primary); }

        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: .85rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,.1);
            font-size: .7rem; color: rgba(147,197,253,.7); text-align: center;
        }

        /* ── Main wrapper ── */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            display: flex; flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ── */
        #topbar {
            position: sticky; top: 0; z-index: 1030;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .85rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        #topbar .page-title   { font-size: 1rem; font-weight: 600; color: #0f172a; margin: 0; }
        #topbar .breadcrumb   { font-size: .72rem; margin: 0; padding: 0; background: none; }
        #topbar .breadcrumb-item, #topbar .breadcrumb-item a { color: #94a3b8; }
        #topbar .breadcrumb-item.active { color: #64748b; }
        #topbar .avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: .8rem;
        }

        /* ── Cards ── */
        .card { border: 1px solid #e2e8f0; border-radius: .85rem; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header {
            padding: .9rem 1.4rem;
            border-bottom: 1px solid #f1f5f9;
            border-radius: .85rem .85rem 0 0 !important;
            background: #fff;
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-header .card-title { font-size: .92rem; font-weight: 600; color: #1e293b; margin: 0; }
        .card-body { padding: 1.25rem 1.4rem; }

        /* ── Stat cards ── */
        .stat-card { border-radius: .85rem; padding: 1.4rem; }
        .stat-card .stat-icon {
            width: 46px; height: 46px; border-radius: .65rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }
        .stat-card .stat-value { font-size: 1.9rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: .78rem; }

        /* ── Tables ── */
        .table thead th {
            font-size: .72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: #64748b; background: #f8fafc;
            border-bottom: 1px solid #e2e8f0; padding: .7rem 1rem; white-space: nowrap;
        }
        .table td { padding: .75rem 1rem; vertical-align: middle; font-size: .855rem; color: #334155; border-bottom: 1px solid #f1f5f9; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table-hover tbody tr:hover td { background: #f8fafc; }

        /* ── Badges ── */
        .badge-draft     { background: #fef9c3; color: #a16207; border: 1px solid #fde047; font-weight: 600; font-size: .72rem; }
        .badge-published { background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 600; font-size: .72rem; }
        .badge-pending   { background: #fef9c3; color: #a16207; border: 1px solid #fde047; font-weight: 600; font-size: .72rem; }
        .badge-approved  { background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 600; font-size: .72rem; }
        .badge-rejected  { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-weight: 600; font-size: .72rem; }
        .badge-paid      { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; font-weight: 600; font-size: .72rem; }
        .badge-pill { padding: .3em .75em; border-radius: 50rem; }

        /* ── Forms ── */
        .form-control, .form-select {
            border-radius: .5rem; border: 1px solid #cbd5e1;
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }
        .form-label { font-size: .82rem; font-weight: 500; color: #374151; margin-bottom: .4rem; }
        .input-group-text { border-radius: .5rem; border: 1px solid #cbd5e1; background: #f8fafc; font-size: .875rem; }

        /* ── Buttons ── */
        .btn { border-radius: .5rem; font-size: .845rem; font-weight: 500; padding: .42rem .95rem; }
        .btn-sm { font-size: .78rem; padding: .3rem .7rem; }
        .btn-primary   { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }

        /* ── Alerts ── */
        .alert { border-radius: .65rem; font-size: .875rem; }

        /* ── Quick-action cards ── */
        .action-card {
            border-radius: .85rem; padding: 1.5rem;
            border: 2px dashed #e2e8f0;
            text-decoration: none; display: block;
            transition: border-color .2s, box-shadow .2s;
        }
        .action-card:hover { border-color: var(--primary); box-shadow: 0 4px 16px rgba(37,99,235,.1); }

        /* ── Responsive ── */
        @media (max-width: 991px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-wrapper { margin-left: 0; }
            #sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1039; display: none; }
            #sidebar-overlay.show { display: block; }
        }

        @media print {
            #sidebar, #topbar, .no-print { display: none !important; }
            #main-wrapper { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div id="sidebar-overlay" onclick="toggleSidebar()"></div>

{{-- ── Sidebar ── --}}
<aside id="sidebar">
    @php
        $__appLogo    = \App\Models\AppSetting::get('app_logo');
        $__appName    = \App\Models\AppSetting::get('app_name', 'LIM Management');
        $__appTagline = \App\Models\AppSetting::get('app_tagline', 'Sistem Penggajian');
    @endphp
    <div class="brand">
        <div class="brand-icon" @if($__appLogo) style="background:#fff;padding:4px;overflow:hidden" @endif>
            @if($__appLogo)
                <img src="{{ asset('storage/'.$__appLogo) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain">
            @else
                <i class="bi bi-briefcase-fill"></i>
            @endif
        </div>
        <div>
            <div class="brand-title">{{ $__appName }}</div>
            <div class="brand-sub">{{ $__appTagline }}</div>
        </div>
    </div>

    <div class="nav-section">Menu Utama</div>
    <ul class="nav flex-column mb-2" style="list-style:none;padding:0">
        @auth
        @if(auth()->user()->isEmployee())
            {{-- ── Employee Portal Nav ── --}}
            <li class="nav-item">
                <a href="{{ route('my.dashboard') }}" class="{{ request()->routeIs('my.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-fill"></i> Beranda Saya
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my.slips') }}" class="{{ request()->routeIs('my.slips') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i> Slip Gaji Saya
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my.appreciation') }}" class="{{ request()->routeIs('my.appreciation*') ? 'active' : '' }}">
                    <i class="bi bi-stars"></i> Dana Apresiasi
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my.reimbursements') }}" class="{{ request()->routeIs('my.reimbursements*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Reimbursement Saya
                </a>
            </li>
            <li style="padding:.75rem 1.25rem .25rem"><div class="nav-section" style="padding:0">Info &amp; Layanan</div></li>
            <li class="nav-item">
                <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    @php $empUnread = auth()->user()->unreadNotifications()->count(); @endphp
                    <i class="bi bi-bell"></i> Notifikasi
                    @if($empUnread > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $empUnread }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my.calendar') }}" class="{{ request()->routeIs('my.calendar') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Kalender
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my.leaves') }}" class="{{ request()->routeIs('my.leaves*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Cuti &amp; Izin
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my.announcements') }}" class="{{ request()->routeIs('my.announcements') ? 'active' : '' }}">
                    <i class="bi bi-megaphone"></i> Pengumuman
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my.requests') }}" class="{{ request()->routeIs('my.requests*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-paper"></i> Permohonan Saya
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my.overtime') }}" class="{{ request()->routeIs('my.overtime*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Pengajuan Lembur
                </a>
            </li>
        @else
            {{-- ── Admin / HR Nav ── --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('companies.index') }}" class="{{ request()->routeIs('companies.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Perusahaan
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Karyawan
                </a>
            </li>
            <li style="padding:.75rem 1.25rem .25rem"><div class="nav-section" style="padding:0">Penggajian</div></li>
            <li class="nav-item">
                <a href="{{ route('payroll-info.index') }}" class="{{ request()->routeIs('payroll-info.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i> Info Penggajian
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('payroll-slips.index') }}" class="{{ request()->routeIs('payroll-slips.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i> Slip Gaji
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('overtime.index') }}" class="{{ request()->routeIs('overtime.*') ? 'active' : '' }}">
                    @php $pendingOT = \App\Models\OvertimeRequest::where('status','pending')->count(); @endphp
                    <i class="bi bi-clock-history"></i> Pengajuan Lembur
                    @if($pendingOT > 0)
                        <span class="badge bg-warning text-dark rounded-pill ms-auto" style="font-size:.65rem">{{ $pendingOT }}</span>
                    @endif
                </a>
            </li>
            <li style="padding:.75rem 1.25rem .25rem"><div class="nav-section" style="padding:0">Bonus &amp; Apresiasi</div></li>
            <li class="nav-item">
                <a href="{{ route('bonuses.index') }}" class="{{ request()->routeIs('bonuses.*') ? 'active' : '' }}">
                    <i class="bi bi-gift"></i> Bonus Karyawan
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('appreciation.index') }}" class="{{ request()->routeIs('appreciation.*') ? 'active' : '' }}">
                    <i class="bi bi-stars"></i> Uang Apresiasi
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('reimbursements.index') }}" class="{{ request()->routeIs('reimbursements.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Reimbursement
                </a>
            </li>
            <li style="padding:.75rem 1.25rem .25rem"><div class="nav-section" style="padding:0">Bisnis B2B</div></li>
            <li class="nav-item">
                <a href="{{ route('b2b.dashboard') }}" class="{{ request()->routeIs('b2b.*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard B2B
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <i class="bi bi-briefcase"></i> Klien
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    <i class="bi bi-kanban"></i> Project
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('quotations.index') }}" class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Quotation
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    @php $invOverdue = \App\Models\Invoice::where('status','overdue')->count(); @endphp
                    <i class="bi bi-file-earmark-spreadsheet"></i> Invoice
                    @if($invOverdue > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $invOverdue }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('bank-accounts.index') }}" class="{{ request()->routeIs('bank-accounts.*') ? 'active' : '' }}">
                    <i class="bi bi-bank"></i> Rekening Bank
                </a>
            </li>
            <li style="padding:.75rem 1.25rem .25rem"><div class="nav-section" style="padding:0">SDM &amp; Komunikasi</div></li>
            <li class="nav-item">
                <a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Kalender Libur
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('leaves.index') }}" class="{{ request()->routeIs('leaves.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Cuti Karyawan
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}">
                    <i class="bi bi-megaphone"></i> Pengumuman
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('internal-requests.index') }}" class="{{ request()->routeIs('internal-requests.*') ? 'active' : '' }}">
                    @php $pendingCount = \App\Models\InternalRequest::where('status','pending')->count(); @endphp
                    <i class="bi bi-inbox"></i> Permohonan Karyawan
                    @if($pendingCount > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li style="padding:.75rem 1.25rem .25rem"><div class="nav-section" style="padding:0">Administrasi</div></li>
            <li class="nav-item">
                <a href="{{ route('master-data.index') }}" class="{{ request()->routeIs('master-data.*') ? 'active' : '' }}">
                    <i class="bi bi-list-ul"></i> Data Master
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('cms.index') }}" class="{{ request()->routeIs('cms.*') ? 'active' : '' }}">
                    <i class="bi bi-palette"></i> CMS
                </a>
            </li>
            @if(auth()->user()->canManageUsers())
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> Kelola Pengguna
                </a>
            </li>
            @endif
        @endif
        @endauth
    </ul>

    <div class="sidebar-footer">© {{ date('Y') }} LIM Management</div>
</aside>

{{-- ── Main Wrapper ── --}}
<div id="main-wrapper">

    {{-- Topbar --}}
    <header id="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-lg-none border-0" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                @if(View::hasSection('breadcrumb'))
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mt-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Home</a></li>
                        <li class="breadcrumb-item active">@yield('breadcrumb')</li>
                    </ol>
                </nav>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 d-none d-sm-inline" style="font-size:.72rem">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->isoFormat('D MMM YYYY') }}
            </span>
            @auth
            {{-- ── Notification Bell ── --}}
            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
            <div class="dropdown">
                <button class="btn btn-sm position-relative" data-bs-toggle="dropdown" aria-label="Notifikasi" title="Notifikasi"
                        style="width:36px;height:36px;padding:0;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#f1f5f9;border:1px solid #e2e8f0;color:#475569">
                    <i class="bi bi-bell-fill" style="font-size:.95rem"></i>
                    @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;padding:.22em .42em;min-width:17px;border:2px solid #fff">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow" style="width:340px;padding:0;border-radius:.85rem;overflow:hidden;border:1px solid #e2e8f0">
                    {{-- Header --}}
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="background:#f8fafc">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold" style="font-size:.875rem;color:#1e293b">Notifikasi</span>
                            @if($unreadCount > 0)
                            <span class="badge rounded-pill" style="background:#dbeafe;color:#1d4ed8;font-size:.65rem;padding:.25em .55em">{{ $unreadCount }} baru</span>
                            @endif
                        </div>
                        @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}" class="mb-0">
                            @csrf @method('PATCH')
                            <button class="btn btn-link btn-sm p-0" style="font-size:.74rem;color:#2563eb;text-decoration:none">Tandai Dibaca</button>
                        </form>
                        @endif
                    </div>
                    {{-- Items --}}
                    @php $recentNotifs = auth()->user()->notifications()->latest()->limit(6)->get(); @endphp
                    @forelse($recentNotifs as $notif)
                    @php
                        $nUnread = is_null($notif->read_at);
                        $nType   = $notif->data['type'] ?? '';
                        $nStatus = $notif->data['status'] ?? '';
                        $nIconCls = match($nType) {
                            'leave_request_new'          => 'bi-calendar-plus',
                            'leave_status_changed'       => $nStatus === 'approved' ? 'bi-calendar-check' : 'bi-calendar-x',
                            'internal_request_new'       => 'bi-inbox',
                            'internal_request_responded' => 'bi-reply-fill',
                            default                      => 'bi-bell',
                        };
                        $nIconBg = match($nType) {
                            'leave_request_new'          => '#2563eb',
                            'leave_status_changed'       => $nStatus === 'approved' ? '#16a34a' : '#dc2626',
                            'internal_request_new'       => '#d97706',
                            'internal_request_responded' => '#0891b2',
                            default                      => '#64748b',
                        };
                    @endphp
                    <a href="{{ route('notifications.read', $notif->id) }}"
                       class="d-flex gap-2 align-items-start px-3 py-2 border-bottom text-decoration-none"
                       style="background:{{ $nUnread ? '#eff6ff' : '#fff' }};color:#1e293b;transition:background .15s">
                        <div class="flex-shrink-0" style="width:34px;height:34px;border-radius:50%;background:{{ $nIconBg }};display:flex;align-items:center;justify-content:center;margin-top:2px">
                            <i class="bi {{ $nIconCls }}" style="font-size:.85rem;color:#fff"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="text-truncate" style="font-size:.8rem;font-weight:{{ $nUnread ? '600' : '400' }};color:#1e293b">{{ $notif->data['title'] ?? 'Notifikasi' }}</div>
                            <div class="text-truncate" style="font-size:.73rem;color:#64748b">{{ $notif->data['message'] ?? '' }}</div>
                            <div style="font-size:.7rem;color:#94a3b8">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                        @if($nUnread)
                        <div class="flex-shrink-0" style="width:8px;height:8px;border-radius:50%;background:#2563eb;margin-top:.45rem"></div>
                        @endif
                    </a>
                    @empty
                    <div class="text-center py-5" style="color:#94a3b8;font-size:.82rem">
                        <i class="bi bi-bell-slash d-block mb-2" style="font-size:2rem;opacity:.3"></i>
                        Belum ada notifikasi
                    </div>
                    @endforelse
                    {{-- Footer --}}
                    <div class="px-3 py-2 text-center border-top" style="background:#f8fafc">
                        <a href="{{ route('notifications.index') }}" class="text-decoration-none" style="font-size:.78rem;color:#2563eb;font-weight:500">
                            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="dropdown">
                <button class="btn btn-sm btn-light border d-flex align-items-center gap-2 px-2" data-bs-toggle="dropdown">
                    <div class="avatar" style="width:28px;height:28px;font-size:.72rem;background:#2563eb;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="d-none d-md-inline" style="font-size:.82rem;font-weight:500;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()->name }}</span>
                    @if(auth()->user()->role)
                    <span class="badge bg-{{ auth()->user()->role->badgeColor() }} bg-opacity-10 text-{{ auth()->user()->role->badgeColor() }} d-none d-lg-inline" style="font-size:.62rem">{{ auth()->user()->role->label() }}</span>
                    @endif
                    <i class="bi bi-chevron-down" style="font-size:.65rem"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:200px;font-size:.84rem">
                    <li><div class="px-3 py-2 border-bottom">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ auth()->user()->email }}</div>
                    </div></li>
                    @if(auth()->user()->canManageUsers())
                    <li><a class="dropdown-item" href="{{ route('users.index') }}"><i class="bi bi-shield-lock me-2"></i>Kelola Pengguna</a></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
            @endauth
        </div>
    </header>

    {{-- Payroll Transfer Reminder --}}
    @auth
    @if(!auth()->user()->isEmployee())
    @php
        $payrollReminders = \App\Models\PayrollSlip::with('company')
            ->where('status', 'published')
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('payment_date')
            ->get()
            ->groupBy('payment_date');
    @endphp
    @if($payrollReminders->isNotEmpty())
    <div class="px-4 pt-3">
        <div class="alert alert-warning d-flex align-items-start gap-3 py-3 mb-0" role="alert" style="border-radius:.75rem;border-left:4px solid #f59e0b;background:#fffbeb">
            <div class="flex-shrink-0" style="width:36px;height:36px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;color:#d97706">
                <i class="bi bi-bell-fill" style="font-size:1rem"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1" style="font-size:.875rem;color:#92400e">Pengingat Transfer Gaji</div>
                <div style="font-size:.82rem;color:#78350f">
                    @foreach($payrollReminders as $date => $slips)
                    @php
                        $d = \Carbon\Carbon::parse($date);
                        $daysLeft = (int) now()->startOfDay()->diffInDays($d->startOfDay(), false);
                        $label = $daysLeft === 0 ? '<span style="color:#dc2626;font-weight:700">HARI INI</span>'
                               : ($daysLeft === 1 ? '<span style="color:#d97706;font-weight:600">Besok</span>'
                               : '<span>'.($daysLeft).' hari lagi</span>');
                    @endphp
                    <div class="d-flex align-items-center gap-2 {{ !$loop->last ? 'mb-1' : '' }}">
                        <i class="bi bi-calendar-event" style="color:#d97706"></i>
                        <strong>{{ $d->isoFormat('D MMM YYYY') }}</strong> — {!! $label !!} —
                        {{ $slips->count() }} slip
                        ({{ $slips->pluck('company.name')->unique()->implode(', ') }})
                    </div>
                    @endforeach
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif
    @endif
    @endauth

    {{-- Flash Messages --}}
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 py-2" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning d-flex align-items-center gap-2 py-2" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <span>{{ session('warning') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Page Content --}}
    <main class="p-4 flex-grow-1">
        @yield('content')
    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebar-overlay').classList.toggle('show');
}
</script>
@stack('scripts')
</body>
</html>
