<!DOCTYPE html>
<html lang="es">
<head>
<title>Verificación de Dos Factores</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="icon" type="image/png" href="{{ asset('login-assets/images/logo-circle.png') }}"/>

<link rel="stylesheet" href="{{ asset('login-assets/vendor/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/fonts/Linearicons-Free-v1.0.0/icon-font.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/vendor/animate/animate.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/vendor/css-hamburgers/hamburgers.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/vendor/animsition/css/animsition.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/vendor/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/vendor/daterangepicker/daterangepicker.css') }}">
<link rel="stylesheet" href="{{ asset('login-assets/css/main.css') }}">

<style>

:root{
--button-color:#0a4898;
}

.btn-show-pass{
display:none!important;
}

.wrap-login100{
width:420px;
max-width:95%;
border-radius:16px;
}

.login100-form{
border-radius:16px;
padding:44px 38px 40px;
box-shadow:0 18px 45px rgba(0,0,0,.28);
border:1px solid rgba(255,255,255,.5);
backdrop-filter:blur(3px);
}

.login100-form-title{
font-size:22px;
line-height:1.4;
margin-bottom:18px;
color:#1f2933;
}

.login-logo-container{
margin-bottom:24px;
}

.container-login100::before{
background-color:#222c5e80;
}

#2fa-error{
margin-top:10px;
margin-bottom:6px;
padding:8px 10px;
font-size:13px;
}

.wrap-input100{
margin-bottom:10px;
position:relative;
}

.input100{
font-size:18px;
padding-left:70px;
transition:all .25s ease;
text-align:center;
letter-spacing:4px;
font-size:24px;
font-weight:bold;
}

.input100:focus{
outline:none!important;
border:none!important;
box-shadow:none!important;
}

.login100-form .focus-input100::after{
color:black!important;
}

.login100-form .focus-input100::before{
background:#222c5e!important;
height:2px;
}

.login100-form .input100:focus{
padding-left:60px;
}

.login100-form .has-val.input100{
padding-left:60px;
}

.container-login100-form-btn {
width: 100%;
display: flex;
flex-wrap: wrap;
justify-content: center;
padding-t: 18px;
padding-b: 18px;
}

.login100-form-btn {
font-size: 15px;
line-height: 1.5;
border-radius: 3px;
background: var(--button-color);
color: #fff;
text-transform: uppercase;
width: 100%;
height: 50px;
border: none;
display: flex;
justify-content: center;
align-items: center;
padding: 0 25px;
-webkit-transition: all 0.4s;
-o-transition: all 0.4s;
-moz-transition: all 0.4s;
transition: all 0.4s;
cursor: pointer;
}

.login100-form-btn:hover{
background: #0a3d7a;
}

.info-2fa {
background: rgba(34, 44, 94, 0.15);
border-left: 4px solid #222c5e;
padding: 10px 15px;
border-radius: 4px;
margin-bottom: 20px;
font-size: 13px;
color: #333;
}

.info-2fa-icon {
color: #222c5e;
font-weight: bold;
margin-right: 8px;
}

</style>
</head>

<body>

<div class="limiter">
<div class="container-login100" style="background-image:url('{{ asset('login-assets/images/bg-01.jpg') }}');">

<div class="wrap-login100 p-t-30 p-b-50">

<form id="verify2fa-form" class="login100-form validate-form" method="POST" action="{{ route('verificar.2fa.post') }}">
@csrf

<span class="login100-form-title p-b-10">
Verificación de Dos Pasos
</span>

<div class="login-logo-container" style="text-align:center;">
<i class="fa fa-shield" style="font-size:60px;color:#222c5e;"></i>
</div>

<div class="info-2fa">
<span class="info-2fa-icon">ℹ</span>
Ingresa el código de 6 dígitos que recibiste por correo. Este código vence en {{ $expira_en_minutos ?? 10 }} minuto{{ (int)($expira_en_minutos ?? 10) !== 1 ? 's' : '' }}.
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 12px;">
        {{ $errors->first() }}
    </div>
@endif

<div id="2fa-error" class="alert alert-danger" style="display:none;"></div>

<div class="wrap-input100">

<input maxlength="6"
class="input100"
type="text"
name="codigo2fa"
id="codigo2fa"
placeholder="000000"
pattern="[0-9]{6}"
autocomplete="off"
oninput="
let v=this.value.replace(/[^0-9]/g,'');
if(v.length > 6){
    v=v.slice(0,6);
}
this.value=v;
">

<span class="focus-input100" data-placeholder="&#xe82a;"></span>

</div>

<div class="container-login100-form-btn">

<button type="submit" class="login100-form-btn" id="verify-button">
Verificar Código
</button>

</div>

<div class="container-login100-form-btn" style="margin-top: 10px;">
<small style="color: #666;">
¿No recibiste el código? Regresa al <a href="{{ route('login') }}" style="color: #222c5e;">login</a>
</small>
</div>

</form>

</div>
</div>
</div>

<script src="{{ asset('login-assets/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
<script src="{{ asset('login-assets/vendor/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('login-assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('login-assets/js/unsaved-guard.js') }}"></script>

<script>

const form = document.getElementById("verify2fa-form");
const button = document.getElementById("verify-button");
const codeInput = document.getElementById("codigo2fa");
const errorBox = document.getElementById("2fa-error");

function showError(msg) {
    errorBox.textContent = msg;
    errorBox.style.display = "block";
}

function hideError() {
    errorBox.style.display = "none";
}

codeInput.addEventListener("input", function() {
    hideError();
});

form.addEventListener("submit", function(e) {
    const code = codeInput.value.trim();
    
    if (code.length !== 6) {
        e.preventDefault();
        showError("El código debe tener exactamente 6 dígitos.");
        return false;
    }
    
    if (!/^\d{6}$/.test(code)) {
        e.preventDefault();
        showError("El código solo debe contener números.");
        return false;
    }
});

// Auto-focus al cargar
document.addEventListener("DOMContentLoaded", function() {
    codeInput.focus();
});

</script>

</body>
</html>
