<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') — Nexus HR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#253D90;--accent:#E3EDF9;}
        body{font-family:'Inter',sans-serif;min-height:100vh;background:linear-gradient(135deg,#253D90 0%,#1a2d6b 50%,#0f1c42 100%);display:flex;align-items:center;justify-content:center;padding:1rem;}
        .auth-wrap{width:100%;max-width:440px;}
        .auth-brand{text-align:center;margin-bottom:2rem;}
        .auth-logo{width:64px;height:64px;background:rgba(255,255,255,.15);border-radius:18px;display:inline-flex;align-items:center;justify-content:center;font-size:1.8rem;color:#fff;margin-bottom:1rem;border:1px solid rgba(255,255,255,.2);}
        .auth-brand h1{font-size:1.6rem;font-weight:800;color:#fff;margin:0;}
        .auth-brand p{color:rgba(255,255,255,.6);font-size:.9rem;margin-top:.3rem;}
        .auth-card{background:#fff;border-radius:20px;padding:2.25rem;box-shadow:0 24px 60px rgba(0,0,0,.25);}
        .auth-card h2{font-size:1.25rem;font-weight:700;color:#1a202c;margin-bottom:.3rem;}
        .auth-card .sub{font-size:.85rem;color:#6c757d;margin-bottom:1.75rem;}
        .form-label{font-size:.83rem;font-weight:600;color:#374151;}
        .form-control{border-radius:10px;border-color:#d1d5db;padding:.6rem .9rem;font-size:.9rem;}
        .form-control:focus{border-color:#253D90;box-shadow:0 0 0 .2rem rgba(37,61,144,.15);}
        .input-group .input-group-text{background:#f8fafc;border-color:#d1d5db;border-radius:10px 0 0 10px;}
        .input-group .form-control{border-radius:0 10px 10px 0;}
        .btn-auth{width:100%;padding:.7rem;border-radius:10px;font-weight:700;background:#253D90;border-color:#253D90;font-size:.95rem;}
        .btn-auth:hover{background:#1a2d6b;border-color:#1a2d6b;}
        .auth-footer{text-align:center;margin-top:1.25rem;font-size:.83rem;color:#6c757d;}
        .auth-footer a{color:#253D90;font-weight:600;text-decoration:none;}
        .auth-footer a:hover{text-decoration:underline;}
        .demo-box{background:#E3EDF9;border-radius:10px;padding:.9rem 1rem;margin-bottom:1.25rem;font-size:.78rem;}
        .demo-box strong{color:#253D90;display:block;margin-bottom:.4rem;}
        .demo-row{display:flex;justify-content:space-between;margin-bottom:.2rem;}
        .alert{border-radius:10px;font-size:.83rem;}
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-brand">
        <div class="auth-logo"><i class="bi bi-people-fill"></i></div>
        <h1>Nexus HR</h1>
        <p>Human Resource Management System</p>
    </div>
    <div class="auth-card">@yield('content')</div>
    <div class="auth-footer mt-3" style="color:rgba(255,255,255,.4);">© {{ date('Y') }} Nexus HR. All rights reserved.</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
