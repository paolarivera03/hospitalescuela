<!DOCTYPE html>
<html lang="es">
<head>
<title>Recuperar contraseña</title>
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
:root{--button-color:#0a4898;}

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

.container-login100::before{
background-color:#222c5e80;
}

.wrap-input100{
margin-bottom:10px;
}

.input100{
font-size:18px;
padding-left:70px;
transition:all .25s ease;
}

.input100:focus{
outline:none!important;
border:none!important;
box-shadow:none!important;
}

.login100-form .focus-input100::after{
color:black!important;
}

.login100-form .input100:focus + .focus-input100::after{
color:black!important;
}

.login100-form .has-val.input100 + .focus-input100::after{
color:black!important;
}

.login100-form .focus-input100::before{
background:#222c5e!important;
height:2px;
}

.login100-form .input100:focus + .focus-input100::before{
background:#222c5e!important;
}

.login100-form .has-val.input100 + .focus-input100::before{
background:#222c5e!important;
}

.login100-form .input100:focus{
padding-left:60px;
}

.login100-form .has-val.input100{
padding-left:60px;
}

.login100-form-btn{
font-size:16px;
text-transform:uppercase;
letter-spacing:.5px;
border-radius:999px;
background:var(--button-color)!important;
color:#ffffff!important;
box-shadow:0 12px 24px var(--button-color);
border:none;
transition:transform .18s ease, box-shadow .18s ease;
}

.login100-form-btn:hover{
transform:translateY(-1px) scale(1.02);
box-shadow:0 16px 30px var(--button-color);
}

.login100-form-btn:active{
transform:translateY(0) scale(.99);
box-shadow:0 10px 20px var(--button-color);
}

.container-login100-form-btn{
margin-top:18px;
}


/* BOTON VER CONTRASEÑA */

#togglePassword{
position:absolute;
right:15px;
top:50%;
transform:translateY(-50%);
cursor:pointer;
font-size:18px;
color:#999;
}

#togglePassword:hover{
color:#008cff;
}
</style>
</head>

<body>

<div class="limiter">
<div class="container-login100" style="background-image:url('{{ asset('login-assets/images/bg-01.jpg') }}');">

<div class="wrap-login100 p-t-30 p-b-50">

<form id="recover-form" class="login100-form validate-form p-b-33 p-t-5" method="POST" action="{{ route('recuperar-contrasena.validar') }}">
@csrf

<span class="login100-form-title p-b-41">
Recuperar contraseña
</span>

<div style="text-align:center;margin-bottom:20px;">
<img src="{{ asset('login-assets/images/logo-circle.png') }}" width="120">
</div>

@if($errors->any())
<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>
@endif

@if(session('status'))
<div class="alert alert-success mt-3">{{ session('status') }}</div>
@endif

<div id="recover-error" class="alert alert-danger mt-3" style="display:none;"></div>

<div class="wrap-input100 validate-input">

<input
maxlength="{{ $max_usuario ?? 10 }}"
class="input100"
type="text"
name="usuario"
id="usuario"
placeholder="Usuario"
style="text-transform:uppercase;"
pattern="[A-Z0-9]+"
autocomplete="off"
onpaste="return false;"
oninput="
let v=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');
const minUsuario={{ $min_usuario ?? 1 }};
const maxUsuario={{ $max_usuario ?? 10 }};
if(v.length > maxUsuario){
v=v.slice(0,maxUsuario);
showRecoverError(`El usuario debe tener entre ${minUsuario} y ${maxUsuario} caracteres.`);
}
this.value=v;
">

<span class="focus-input100" data-placeholder="&#xe82a;"></span>

</div>

<small style="color:#fff;">
Primero valida tu usuario y luego continúa con la recuperación por correo.
</small>
<small style="color:#fff; display:block; margin-top:8px;">
Se enviará una contraseña temporal al correo registrado para completar el cambio de contraseña.
</small>

<div class="container-login100-form-btn">
<button type="submit" class="login100-form-btn" id="recover-button">
Continuar
</button>
</div>

<div class="container-login100-form-btn">
<a class="login100-form-btn"
href="{{ url('/login') }}"
style="text-decoration:none;display:flex;justify-content:center;">
Volver al Login
</a>
</div>

</form>

</div>
</div>
</div>

<script src="{{ asset('login-assets/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
<script src="{{ asset('login-assets/vendor/animsition/js/animsition.min.js') }}"></script>
<script src="{{ asset('login-assets/vendor/bootstrap/js/popper.js') }}"></script>
<script src="{{ asset('login-assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('login-assets/vendor/select2/select2.min.js') }}"></script>
<script src="{{ asset('login-assets/vendor/daterangepicker/moment.min.js') }}"></script>
<script src="{{ asset('login-assets/vendor/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('login-assets/vendor/countdowntime/countdowntime.js') }}"></script>
<script src="{{ asset('login-assets/js/main.js') }}"></script>
<script src="{{ asset('login-assets/js/unsaved-guard.js') }}"></script>

<script>

const recoverForm=document.getElementById("recover-form");
const recoverButton=document.getElementById("recover-button");
const recoverErrorBox=document.getElementById("recover-error");

const usuarioInput=document.getElementById("usuario");

/* BLOQUEAR HOLD KEY */

function blockKeyRepeat(input){
input.addEventListener("keydown",function(e){
if(e.repeat){
e.preventDefault();
}
});
}

blockKeyRepeat(usuarioInput);

/* MENSAJES */

function showRecoverError(msg){
recoverErrorBox.style.display="block";
recoverErrorBox.textContent=msg;
}

function clearRecoverError(){
recoverErrorBox.style.display="none";
recoverErrorBox.textContent="";
}

/* SUBMIT */

recoverForm.addEventListener("submit", function(e){

e.preventDefault();

clearRecoverError();

const usuario=usuarioInput.value.trim().toUpperCase();
const usuarioRaw=usuarioInput.value;

const minUsuario={{ $min_usuario ?? 1 }};
const maxUsuario={{ $max_usuario ?? 10 }};

if(!usuario){
showRecoverError("Ingresa tu usuario.");
return;
}

if(/\s/.test(usuarioRaw)){
showRecoverError("El usuario no debe contener espacios en blanco.");
return;
}

if(usuario.length < minUsuario || usuario.length > maxUsuario){
showRecoverError(`El usuario debe tener entre ${minUsuario} y ${maxUsuario} caracteres.`);
return;
}

recoverButton.disabled=true;
recoverButton.textContent="Validando...";
recoverForm.submit();

});

document.querySelectorAll(".togglePassword").forEach(function(btn){

btn.addEventListener("click",function(){

const targetId=this.getAttribute("data-target");
const input=document.getElementById(targetId);
const icon=this.querySelector("i");

const type=input.getAttribute("type")==="password"?"text":"password";

input.setAttribute("type",type);

icon.classList.toggle("fa-eye");
icon.classList.toggle("fa-eye-slash");

});

});

</script>

</body>
</html>




