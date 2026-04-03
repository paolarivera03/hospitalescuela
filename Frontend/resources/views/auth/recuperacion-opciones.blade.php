<!DOCTYPE html>
<html lang="es">
<head>
<title>Opciones de recuperación</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="icon" type="image/png" href="{{ asset('login-assets/images/logo-circle.png') }}"/>
<link rel="stylesheet" href="{{ asset('login-assets/vendor/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/vendor/animate/animate.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/vendor/animsition/css/animsition.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/css/main.css') }}">

<style>
:root{--button-color:#0a4898;--panel-color:#222c5e;}
.wrap-login100{width:460px;max-width:95%;border-radius:16px;}
.login100-form{border-radius:16px;padding:44px 38px 40px;box-shadow:0 18px 45px rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.5);backdrop-filter:blur(3px);}
.login100-form-title{font-size:22px;line-height:1.4;margin-bottom:18px;color:#1f2933;}
.container-login100::before{background-color:#222c5e80;}
.login100-form-btn{font-size:16px;text-transform:uppercase;letter-spacing:.5px;border-radius:999px;background:var(--button-color)!important;color:#ffffff!important;box-shadow:0 12px 24px var(--button-color);border:none;transition:transform .18s ease, box-shadow .18s ease;min-height:54px;}
.login100-form-btn:hover{transform:translateY(-1px) scale(1.02);box-shadow:0 16px 30px var(--button-color);}
.container-login100-form-btn{margin-top:18px;}
.option-card{background:rgba(255,255,255,.92);border-radius:18px;padding:18px 18px 16px;border:1px solid rgba(34,44,94,.15);box-shadow:0 14px 28px rgba(34,44,94,.10);margin-bottom:14px;}
.option-title{font-size:17px;font-weight:700;color:var(--panel-color);margin-bottom:6px;}
.option-copy{font-size:13px;color:#445;line-height:1.5;margin-bottom:12px;}
.user-chip{display:inline-flex;align-items:center;gap:8px;background:rgba(34,44,94,.12);color:var(--panel-color);border-radius:999px;padding:8px 14px;font-size:13px;font-weight:700;margin-bottom:18px;}
.link-btn{display:flex;justify-content:center;align-items:center;text-decoration:none;}
.option-action{width:220px;min-height:44px;font-size:14px;margin:0 auto;box-shadow:0 10px 20px rgba(10,72,152,.35);}
.option-card form{display:flex;justify-content:center;}
</style>
</head>
<body>
<div class="limiter">
<div class="container-login100" style="background-image:url('{{ asset('login-assets/images/bg-01.jpg') }}');">
<div class="wrap-login100 p-t-30 p-b-50">
<div class="login100-form">
<span class="login100-form-title p-b-10">Opciones de recuperación</span>
<div style="text-align:center;margin-bottom:20px;">
<img src="{{ asset('login-assets/images/logo-circle.png') }}" width="120" height="120" style="border-radius:50%;object-fit:cover;">
</div>

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:12px;">{{ $errors->first() }}</div>
@endif

<div class="user-chip">
<i class="fa fa-user"></i>
<span>{{ $usuario }}</span>
</div>

<div class="option-card">
<div class="option-title">Recuperación por correo</div>
<div class="option-copy">Se enviará una contraseña temporal al correo registrado.</div>
<form method="POST" action="{{ route('recuperacion.correo') }}">
@csrf
<button type="submit" class="login100-form-btn option-action">Elegir correo</button>
</form>
</div>

<div class="container-login100-form-btn">
<a class="login100-form-btn link-btn" href="{{ route('recuperar-contrasena') }}">Volver</a>
</div>
</div>
</div>
</div>
</div>
<script src="{{ asset('login-assets/js/unsaved-guard.js') }}"></script>
</body>
</html>
