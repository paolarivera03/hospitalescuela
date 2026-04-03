<!DOCTYPE html>
<html lang="es">
<head>
<title>Cambiar contraseña</title>
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

</style>
</head>

<body>

<div class="limiter">
<div class="container-login100" style="background-image:url('{{ asset('login-assets/images/bg-01.jpg') }}');">

<div class="wrap-login100 p-t-30 p-b-50">

<form id="change-password-form" class="login100-form validate-form">
@csrf

<span class="login100-form-title p-b-10">
Cambiar contraseña
</span>

<div style="text-align:center;margin-bottom:20px;">
<img src="{{ asset('login-assets/images/logo-circle.png') }}" width="120">
</div>

<div id="change-error" class="alert alert-danger" style="display:none;"></div>
<div id="change-success" class="alert alert-success" style="display:none;"></div>

@if(session('status'))
<div class="alert alert-success" style="margin-bottom:12px;">{{ session('status') }}</div>
@endif

@if(($requireCurrentPassword ?? true) === false)
<div class="alert alert-info" style="margin-bottom:12px;">
Puedes cambiar tu contraseña directamente porque la recuperación se realizó por correo.
</div>
@endif

@if(($requireCurrentPassword ?? true) !== false)
<div class="wrap-input100" style="position:relative;">
<input maxlength="{{ $max_contrasena ?? 10 }}"
minlength="{{ $min_contrasena ?? 5 }}"
class="input100"
type="password"
id="contrasena_actual"
placeholder="Contraseña actual"
autocomplete="off"
oninput="
let v=this.value.replace(/\s/g,'');
const minContrasena={{ $min_contrasena ?? 5 }};
const maxContrasena={{ $max_contrasena ?? 10 }};
if(v.length > maxContrasena){
v=v.slice(0,maxContrasena);
}
this.value=v;
">
<span class="focus-input100" data-placeholder="&#xe80f;"></span>
<span class="togglePassword" data-target="contrasena_actual" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999;">
    <i class="fa fa-eye"></i>
</span>
</div>
@endif

<div class="wrap-input100" style="position:relative;">
<input maxlength="{{ $max_contrasena ?? 10 }}"
minlength="{{ $min_contrasena ?? 5 }}"
class="input100"
type="password"
id="contrasena_nueva"
placeholder="Nueva contraseña"
autocomplete="off"
oninput="
let v=this.value.replace(/\s/g,'');
const minContrasena={{ $min_contrasena ?? 5 }};
const maxContrasena={{ $max_contrasena ?? 10 }};
if(v.length > maxContrasena){
v=v.slice(0,maxContrasena);
showChangeError(`La contraseña debe tener entre ${minContrasena} y ${maxContrasena} caracteres.`);
}
this.value=v;
">
<span class="focus-input100" data-placeholder="&#xe80f;"></span>
<span class="togglePassword" data-target="contrasena_nueva" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999;">
    <i class="fa fa-eye"></i>
</span>
</div>

<div class="wrap-input100" style="position:relative;">
<input maxlength="{{ $max_contrasena ?? 10 }}"
minlength="{{ $min_contrasena ?? 5 }}"
class="input100"
type="password"
id="confirmar_contrasena"
placeholder="Confirmar nueva contraseña"
autocomplete="off"
oninput="
let v=this.value.replace(/\s/g,'');
const maxContrasena={{ $max_contrasena ?? 10 }};
if(v.length > maxContrasena){
v=v.slice(0,maxContrasena);
}
this.value=v;
">
<span class="focus-input100" data-placeholder="&#xe80f;"></span>
<span class="togglePassword" data-target="confirmar_contrasena" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999;">
    <i class="fa fa-eye"></i>
</span>
</div>

<small style="color:#fff;">
La nueva contraseña debe tener mayúsculas, minúsculas, números y carácter especial.
</small>

<div class="container-login100-form-btn">
<button type="submit" class="login100-form-btn" id="change-button">
Guardar nueva contraseña
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
<script src="{{ asset('login-assets/vendor/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('login-assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('login-assets/js/unsaved-guard.js') }}"></script>

<script>

const changeApiUrl="http://localhost:3000/api/change-password";
const jwtTokenFromServer="{{ $jwtToken ?? '' }}";

function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

function getJwtToken() {
    // Prefer the server-provided JWT (from session), but fall back to the cookie.
    return jwtTokenFromServer || getCookie('jwt_token') || getCookie('token');
}

const changeForm=document.getElementById("change-password-form");
const changeButton=document.getElementById("change-button");
const changeErrorBox=document.getElementById("change-error");
const changeSuccessBox=document.getElementById("change-success");

const passActual=document.getElementById("contrasena_actual");
const passNueva=document.getElementById("contrasena_nueva");
const passConfirmar=document.getElementById("confirmar_contrasena");
const requireCurrentPassword={{ ($requireCurrentPassword ?? true) ? 'true' : 'false' }};

/* BLOQUEAR HOLD KEY */

function blockKeyRepeat(input){
input.addEventListener("keydown",function(e){
if(e.repeat){
e.preventDefault();
}
});
}

if(passActual){
blockKeyRepeat(passActual);
}
blockKeyRepeat(passNueva);
blockKeyRepeat(passConfirmar);

/* MENSAJES */

function showChangeError(msg){
changeErrorBox.style.display="block";
changeErrorBox.textContent=msg;
}

function clearChangeError(){
changeErrorBox.style.display="none";
changeErrorBox.textContent="";
}

function showChangeSuccess(msg){
changeSuccessBox.style.display="block";
changeSuccessBox.textContent=msg;
}

function clearChangeSuccess(){
changeSuccessBox.style.display="none";
changeSuccessBox.textContent="";
}

function forceLogoutToLogin() {
const csrfToken = document.querySelector('input[name="_token"]')?.value || "";
const form = document.createElement("form");
form.method = "POST";
form.action = "{{ route('logout') }}";

const tokenInput = document.createElement("input");
tokenInput.type = "hidden";
tokenInput.name = "_token";
tokenInput.value = csrfToken;

form.appendChild(tokenInput);
document.body.appendChild(form);
form.submit();
}

/* VALIDAR FUERZA */

function validarPassword(password){
const regex=/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/;
return regex.test(password);
}

/* SUBMIT */

changeForm.addEventListener("submit",async function(e){

e.preventDefault();

clearChangeError();
clearChangeSuccess();

const contrasena_actual = passActual ? passActual.value.trim() : "";
const contrasena_nueva = passNueva.value.trim();
const confirmar_contrasena = passConfirmar.value.trim();

const minContrasena={{ $min_contrasena ?? 5 }};
const maxContrasena={{ $max_contrasena ?? 10 }};

if((requireCurrentPassword && !contrasena_actual) || !contrasena_nueva || !confirmar_contrasena){
showChangeError("Completa todos los campos.");
return;
}

if(contrasena_nueva.length < minContrasena || contrasena_nueva.length > maxContrasena){
showChangeError(`La contraseña debe tener entre ${minContrasena} y ${maxContrasena} caracteres.`);
return;
}

if(!validarPassword(contrasena_nueva)){
showChangeError("Debe incluir mayúscula, minúscula, número y carácter especial.");
return;
}

if(contrasena_nueva !== confirmar_contrasena){
showChangeError("Las contraseñas no coinciden.");
return;
}

changeButton.disabled=true;
    changeButton.textContent="Guardando...";

    try{

        const headers = {
            "Content-Type": "application/json"
        };
        const token = getJwtToken();
        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        const response = await fetch(changeApiUrl, {
            method: "POST",
            credentials: 'include',
            headers,
            body: JSON.stringify({
                contrasena_actual,
                contrasena_nueva,
                confirmar_contrasena
            })
        });

        const data = await response.json();

if (!response.ok) {
    showChangeError(`${data.message || "No se pudo actualizar la contraseña."} (status ${response.status})`);
    if (response.status === 401 || response.status === 403) {
        setTimeout(() => forceLogoutToLogin(), 1200);
    }
    return;
}

showChangeSuccess(data.message || "Contraseña actualizada.");
setTimeout(() => {
    forceLogoutToLogin();
}, 1200);

}catch(err){

console.error(err);
showChangeError("Error conectando con el servidor.");

}finally{

changeButton.disabled=false;
changeButton.textContent="Guardar nueva contraseña";

}

});

// Quitar query params incómodos (por ejemplo _token) para que no aparezcan en la URL
if (window.location.search) {
    const params = new URLSearchParams(window.location.search);
    if (params.has('_token') || params.has('token')) {
        window.history.replaceState(null, '', window.location.pathname);
    }
}

// Toggle mostrar/ocultar contraseña (botoncitos)
document.querySelectorAll(".togglePassword").forEach(function(btn){
    btn.addEventListener("click", function(){
        const targetId = this.getAttribute("data-target");
        const input = document.getElementById(targetId);
        if (!input) return;

        const type = input.getAttribute("type") === "password" ? "text" : "password";
        input.setAttribute("type", type);

        const icon = this.querySelector("i");
        if (icon) {
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        }
    });
});

</script>

</body>
</html>




