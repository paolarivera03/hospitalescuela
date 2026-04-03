<!DOCTYPE html>
<html lang="es">
<head>
<title>Login</title>
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

#login-error{
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

/* BOTONES IGUAL QUE REGISTER */

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
.forgot-link{
color: #0091bd;
text-decoration:none;
font: size 14px;
}

.forgot-link:hover{
color: #004381;
text-decoration:none;
font: size 16px;
}



</style>
</head>

<body>

<div class="limiter">
<div class="container-login100" style="background-image:url('{{ asset('login-assets/images/bg-01.jpg') }}');">

<div class="wrap-login100 p-t-30 p-b-50">

<form id="login-form" class="login100-form validate-form" method="POST" action="{{ route('login.post') }}">
@csrf

<span class="login100-form-title p-b-10">
Sistema de Gestión de Medicamentos
</span>

<div class="login-logo-container" style="text-align:center;">
<img src="{{ asset('login-assets/images/logo-circle.png') }}" width="120" height="120" style="border-radius:50%; object-fit:cover;">
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 12px;">
        {{ $errors->first() }}
    </div>
@endif

@if(session('status'))
    <div class="alert alert-success" style="margin-bottom: 12px;">
        {{ session('status') }}
    </div>
@endif

<div id="login-error" class="alert alert-danger" style="display:none;"></div>

<div class="wrap-input100">

<input maxlength="{{ $max_usuario ?? 10 }}"
class="input100"
type="text"
name="usuario"
id="username"
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
showError(`El usuario debe tener entre ${minUsuario} y ${maxUsuario} caracteres.`);
}
this.value=v;
">

<span class="focus-input100" data-placeholder="&#xe82a;"></span>

</div>

<div class="wrap-input100">

<input maxlength="{{ $max_contrasena ?? 10 }}"
name="password"
id="password-field"
class="input100"
type="password"
placeholder="Contraseña"
autocomplete="off"
oninput="
let v=this.value.replace(/\s/g,'');
const minContrasena={{ $min_contrasena ?? 5 }};
const maxContrasena={{ $max_contrasena ?? 10 }};
if(v.length > maxContrasena){
v=v.slice(0,maxContrasena);
showError(`La contraseña debe tener entre ${minContrasena} y ${maxContrasena} caracteres.`);
}
this.value=v;
">

<span class="focus-input100" data-placeholder="&#xe80f;"></span>

<span id="togglePassword">
<i id="togglePasswordIcon" class="fa fa-eye"></i>
</span>

</div>

<div class="container-login100-form-btn">
<a href="{{ url('/recuperar-contrasena') }}" class="forgot-link">
¿Olvidaste tu contraseña?
</a>
</div>

<div class="container-login100-form-btn">

<button type="submit" class="login100-form-btn" id="login-button">
Iniciar Sesión
</button>

</div>

<div class="container-login100-form-btn">

<a class="login100-form-btn"
href="{{ url('/registro') }}"
style="text-decoration:none;display:flex;justify-content:center;">
Crear Usuario
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

const loginForm=document.getElementById("login-form");
const loginButton=document.getElementById("login-button");
const errorBox=document.getElementById("login-error");

const username=document.getElementById("username");
const password=document.getElementById("password-field");

/* BLOQUEAR HOLD KEY */

function blockKeyRepeat(input){
    input.addEventListener("keydown",function(e){
        if(e.repeat){
            e.preventDefault();
        }
    });
}

blockKeyRepeat(username);
blockKeyRepeat(password);

/* ERRORES */

function showError(msg){
    errorBox.style.display="block";
    errorBox.textContent=msg;
}

function clearError(){
    errorBox.style.display="none";
    errorBox.textContent="";
}

/* LOGIN */

loginForm.addEventListener("submit", function(e){
    e.preventDefault();
    clearError();

    const userRaw = username.value;
    const passRaw = password.value;
    const user = userRaw.trim().toUpperCase();
    const pass = passRaw;

    const minUsuario = {{ $min_usuario ?? 1 }};
    const maxUsuario = {{ $max_usuario ?? 10 }};
    const minContrasena = {{ $min_contrasena ?? 5 }};
    const maxContrasena = {{ $max_contrasena ?? 10 }};

    const missing=[];
    if(!user){ missing.push("usuario"); }
    if(!pass.trim()){ missing.push("contraseña"); }

    if(missing.length>0){
        showError(missing.length===2 ? "Debe ingresar usuario y contraseña." : `Debe ingresar ${missing[0]}.`);
        return;
    }

    if(/\s/.test(userRaw) || /\s/.test(passRaw)){
        showError("Usuario y contraseña no permiten espacios en blanco.");
        return;
    }

    if(user.length < minUsuario || user.length > maxUsuario){
        showError(`El usuario debe tener entre ${minUsuario} y ${maxUsuario} caracteres.`);
        return;
    }

    if(pass.length < minContrasena || pass.length > maxContrasena){
        showError(`La contraseña debe tener entre ${minContrasena} y ${maxContrasena} caracteres.`);
        return;
    }

    loginButton.disabled=true;
    loginButton.textContent="Verificando...";

    // Enviar el formulario al backend de Laravel.
    loginForm.submit();
});

/* MOSTRAR CONTRASEÑA */

const togglePassword=document.getElementById("togglePassword");
const toggleIcon=document.getElementById("togglePasswordIcon");
const passwordField=document.getElementById("password-field");

togglePassword.addEventListener("click",function(){
    const type=passwordField.getAttribute("type")==="password"?"text":"password";
    passwordField.setAttribute("type",type);
    toggleIcon.classList.toggle("fa-eye");
    toggleIcon.classList.toggle("fa-eye-slash");
});

</script>

</body>
</html>




