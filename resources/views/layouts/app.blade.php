<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Nexus HR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#253D90; --primary-dark:#1a2d6b; --accent:#E3EDF9; --sidebar-width:255px; --topbar-height:60px; }
        *{box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:#f0f4f9;color:#1e293b;min-height:100vh;}
        #sidebar{position:fixed;top:0;left:0;width:var(--sidebar-width);height:100vh;background:var(--primary);z-index:1050;display:flex;flex-direction:column;transition:transform .25s ease;}
        .sidebar-brand{height:var(--topbar-height);display:flex;align-items:center;padding:0 1.25rem;border-bottom:1px solid rgba(255,255,255,.08);gap:.75rem;flex-shrink:0;}
        .sidebar-brand-icon{width:34px;height:34px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.95rem;flex-shrink:0;}
        .sidebar-brand-text{font-size:.95rem;font-weight:800;color:#fff;line-height:1.1;}
        .sidebar-brand-sub{font-size:.62rem;color:rgba(255,255,255,.45);}
        .sidebar-scroll{flex:1;overflow-y:auto;padding:.5rem 0 1rem;}
        .sidebar-scroll::-webkit-scrollbar{width:3px;}.sidebar-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:2px;}
        .nav-section{padding:.8rem 1.25rem .2rem;font-size:.6rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1.2px;}
        .sidebar-link{display:flex;align-items:center;padding:.55rem 1.25rem;color:rgba(255,255,255,.7);font-size:.84rem;font-weight:500;text-decoration:none;border-left:3px solid transparent;transition:all .18s;gap:.65rem;}
        .sidebar-link i{font-size:.95rem;width:18px;flex-shrink:0;}
        .sidebar-link:hover,.sidebar-link.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:rgba(255,255,255,.7);}
        .sidebar-link .badge{margin-left:auto;font-size:.62rem;}
        .sidebar-footer{border-top:1px solid rgba(255,255,255,.08);padding:.9rem 1.25rem;flex-shrink:0;}
        .sidebar-user{display:flex;align-items:center;gap:.65rem;margin-bottom:.6rem;}
        .sidebar-avatar{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.15);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;flex-shrink:0;}
        .sidebar-user-name{font-size:.8rem;font-weight:600;color:#fff;}
        .sidebar-user-role{font-size:.67rem;color:rgba(255,255,255,.45);}
        .btn-logout{width:100%;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.7);font-size:.76rem;padding:.35rem .75rem;border-radius:7px;cursor:pointer;transition:all .18s;}
        .btn-logout:hover{background:rgba(255,255,255,.15);color:#fff;}
        #topbar{position:fixed;top:0;left:var(--sidebar-width);right:0;height:var(--topbar-height);background:#fff;z-index:1040;display:flex;align-items:center;padding:0 1.5rem;border-bottom:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,.05);}
        .btn-sidebar-toggle{background:none;border:none;font-size:1.2rem;color:#64748b;cursor:pointer;padding:.3rem .45rem;border-radius:7px;margin-right:.5rem;transition:all .15s;}
        .btn-sidebar-toggle:hover{background:#f1f5f9;}
        .topbar-title{font-size:.95rem;font-weight:700;color:#1e293b;}
        .topbar-right{margin-left:auto;display:flex;align-items:center;gap:.6rem;}
        .ph-clock{font-size:.78rem;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;padding:.3rem .7rem;font-weight:500;}
        #main-content{margin-left:var(--sidebar-width);margin-top:var(--topbar-height);padding:1.75rem;min-height:calc(100vh - var(--topbar-height));transition:margin-left .25s;}
        .card{border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.06);background:#fff;}
        .card-header{background:#fff;border-bottom:1px solid #e2e8f0;border-radius:12px 12px 0 0 !important;padding:.9rem 1.25rem;}
        .card-header h5,.card-header h6{margin:0;font-weight:700;}
        .stat-card{border-radius:12px;padding:1.25rem;border:none;box-shadow:0 1px 6px rgba(0,0,0,.07);background:#fff;transition:transform .2s,box-shadow .2s;}
        .stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(37,61,144,.12);}
        .stat-icon{width:46px;height:46px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;}
        .stat-value{font-size:1.75rem;font-weight:800;line-height:1;margin-top:.7rem;}
        .stat-label{font-size:.76rem;color:#64748b;margin-top:.2rem;font-weight:500;}
        .btn-primary{background:var(--primary);border-color:var(--primary);}
        .btn-primary:hover{background:var(--primary-dark);border-color:var(--primary-dark);}
        .btn-outline-primary{color:var(--primary);border-color:var(--primary);}
        .btn-outline-primary:hover{background:var(--primary);border-color:var(--primary);color:#fff;}
        .table th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0;background:#f8fafc;padding:.65rem 1rem;}
        .table td{vertical-align:middle;font-size:.85rem;padding:.65rem 1rem;}
        .table-hover tbody tr:hover{background:#f8fafc;}
        .emp-avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;background:var(--accent);color:var(--primary);font-weight:700;font-size:.75rem;display:inline-flex;align-items:center;justify-content:center;}
        img.emp-avatar{display:block;}
        .badge{font-size:.68rem;font-weight:600;padding:.28em .6em;}
        .form-label{font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
        .form-control,.form-select{border-radius:8px;border-color:#d1d5db;font-size:.86rem;}
        .form-control:focus,.form-select:focus{border-color:var(--primary);box-shadow:0 0 0 .2rem rgba(37,61,144,.12);}
        .input-group-text{background:#f8fafc;border-color:#d1d5db;font-size:.86rem;}
        .alert{border-radius:10px;border:none;font-size:.85rem;}
        .page-header{margin-bottom:1.4rem;}
        .page-header h1{font-size:1.35rem;font-weight:800;color:#0f172a;}
        .breadcrumb{font-size:.76rem;margin:0;}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1045;}
        @media(max-width:991px){
            #sidebar{transform:translateX(-100%);}
            #sidebar.show{transform:translateX(0);}
            .sidebar-overlay.show{display:block;}
            #topbar{left:0!important;}
            #main-content{margin-left:0!important;padding:1rem;}
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-people-fill"></i></div>
        <div>
            <div class="sidebar-brand-text">Nexus HR</div>
            <div class="sidebar-brand-sub">Human Resources</div>
        </div>
    </div>
    <div class="sidebar-scroll">
        <div class="nav-section">Main</div>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        @if(auth()->user()->hasHrAccess())
        <div class="nav-section">Management</div>
        <a href="{{ route('employees.index') }}" class="sidebar-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Employees
        </a>
        <a href="{{ route('departments.index') }}" class="sidebar-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i> Departments
        </a>
        @endif
        <div class="nav-section">Workforce</div>
        @if(auth()->user()->hasHrAccess())
        <a href="{{ route('attendance.index') }}" class="sidebar-link {{ request()->routeIs('attendance.index','attendance.create','attendance.edit') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Attendance
        </a>
        <a href="{{ route('biometric.index') }}" class="sidebar-link {{ request()->routeIs('biometric.*') ? 'active' : '' }}">
            <i class="bi bi-fingerprint"></i> Biometrics
            @php $unprocessed = \App\Models\BiometricLog::where('processed',false)->count(); @endphp
            @if($unprocessed > 0)<span class="badge bg-warning text-dark rounded-pill">{{ $unprocessed }}</span>@endif
        </a>
        @endif
        <a href="{{ route('leaves.index') }}" class="sidebar-link {{ request()->routeIs('leaves.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-x"></i> Leave Requests
            @if(auth()->user()->hasHrAccess())
                @php $pendingLeaves = \App\Models\Leave::where('status','pending')->count(); @endphp
                @if($pendingLeaves > 0)<span class="badge bg-danger rounded-pill">{{ $pendingLeaves }}</span>@endif
            @endif
        </a>
        @if(auth()->user()->hasPayrollAccess())
        <div class="nav-section">Finance</div>
        <a href="{{ route('payroll.index') }}" class="sidebar-link {{ request()->routeIs('payroll.index','payroll.create','payroll.show','payroll.edit') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i> Payroll
        </a>
        @endif
        @if(auth()->user()->hasRecruiterAccess())
        <div class="nav-section">Recruitment</div>
        <a href="{{ route('recruitment.index') }}" class="sidebar-link {{ request()->routeIs('recruitment.*','applications.*') ? 'active' : '' }}">
            <i class="bi bi-briefcase"></i> Job Postings
        </a>
        @endif
        @if(auth()->user()->employee)
        <div class="nav-section">My Account</div>
        <a href="{{ route('attendance.my') }}" class="sidebar-link {{ request()->routeIs('attendance.my') ? 'active' : '' }}">
            <i class="bi bi-person-check"></i> My Attendance
        </a>
        <a href="{{ route('payroll.my') }}" class="sidebar-link {{ request()->routeIs('payroll.my') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> My Payslips
        </a>
        @endif
        @if(auth()->user()->canManageUsers())
        <div class="nav-section">System</div>
        <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> User Management
        </a>
        @endif
    </div>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
            <div>
                <div class="sidebar-user-name">{{ Str::limit(auth()->user()->name,18) }}</div>
                <div class="sidebar-user-role">{{ auth()->user()->getRoleLabel() }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</button>
        </form>
    </div>
</nav>

<div id="topbar">
    <button class="btn-sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
    <span class="topbar-title">@yield('page-title','Dashboard')</span>
    <div class="topbar-right">
        <div class="ph-clock d-none d-md-block"><i class="bi bi-clock me-1"></i><span id="phClock">--:--:-- --</span> <span class="text-muted" style="font-size:.65rem;">PHT</span></div>
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2 border" data-bs-toggle="dropdown">
                <div class="emp-avatar" style="width:26px;height:26px;font-size:.68rem;background:var(--accent);color:var(--primary);">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
                <span class="d-none d-md-inline" style="font-size:.82rem;font-weight:600;">{{ Str::limit(auth()->user()->name,16) }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size:.85rem;min-width:200px;">
                <li><div class="px-3 py-2 border-bottom">
                    <div class="fw-600 small">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size:.72rem;">{{ auth()->user()->email }}</div>
                    <span class="badge mt-1" style="background:var(--accent);color:var(--primary);font-size:.62rem;">{{ auth()->user()->getRoleLabel() }}</span>
                </div></li>
                @if(auth()->user()->employee)
                <li><a class="dropdown-item" href="{{ route('employees.show',auth()->user()->employee) }}"><i class="bi bi-person me-2 text-muted"></i>My Profile</a></li>
                @endif
                <li><hr class="dropdown-divider my-1"></li>
                <li><form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                </form></li>
            </ul>
        </div>
    </div>
</div>

<div id="main-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2 mb-3">
            <i class="bi bi-check-circle-fill mt-1 flex-shrink-0"></i>
            <div>{!! session('success') !!}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 mb-3">
            <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <div class="d-flex align-items-center gap-2 mb-1"><i class="bi bi-exclamation-triangle-fill"></i><strong>Please fix the following:</strong></div>
            <ul class="mb-0 ps-4 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sidebar=document.getElementById('sidebar'),overlay=document.getElementById('sidebarOverlay'),toggle=document.getElementById('sidebarToggle');
[toggle,overlay].forEach(el=>el?.addEventListener('click',()=>{sidebar.classList.toggle('show');overlay.classList.toggle('show');}));
// Philippine Time (UTC+8)
function updatePHClock(){
    const el=document.getElementById('phClock');if(!el)return;
    const now=new Date();
    const ph=new Date(now.toLocaleString('en-US',{timeZone:'Asia/Manila'}));
    const h=ph.getHours(),m=ph.getMinutes(),s=ph.getSeconds(),ampm=h>=12?'PM':'AM',hr=h%12||12;
    el.textContent=`${String(hr).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')} ${ampm}`;
}
setInterval(updatePHClock,1000);updatePHClock();
setTimeout(()=>{document.querySelectorAll('.alert').forEach(a=>{try{bootstrap.Alert.getOrCreateInstance(a)?.close();}catch(e){}});},6000);
</script>
@stack('scripts')
</body>
</html>
