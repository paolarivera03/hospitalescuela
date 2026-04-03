<!DOCTYPE html>
<html lang="es">
<head>
<title>Verificar preguntas de seguridad</title>
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

.login-logo-container{
margin-bottom:24px;
text-align:center;
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

.section-title{
font-size:13px;
font-weight:700;
letter-spacing:.5px;
text-transform:uppercase;
color:#2c3e6f;
margin:16px 0 10px;
}

.qa-block{
border:1px solid rgba(34,44,94,.14);
border-radius:14px;
padding:12px 12px 4px;
margin-bottom:12px;
background:rgba(255,255,255,.75);
}

.question-label{
display:block;
font-size:14px;
font-weight:600;
color:#1f2933;
margin-bottom:8px;
}

.rule-box{
margin-bottom:12px;
font-size:13px;
border-radius:10px;
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

<form id="verify-questions-form" class="login100-form validate-form" method="POST" action="{{ route('seguridad.verificar.post') }}">
@csrf

<span class="login100-form-title p-b-10">
Verificar preguntas de seguridad
</span>

<div class="login-logo-container">
<img src="{{ asset('login-assets/images/logo-circle.png') }}" width="120" height="120" style="border-radius:50%; object-fit:cover;">
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 12px;">
        {{ $errors->first() }}
    </div>
@endif

<div id="verify-error" class="alert alert-danger" style="display:none;"></div>

<div class="alert alert-secondary rule-box">
    Cada respuesta debe tener entre {{ $min_respuesta ?? 5 }} y {{ $max_respuesta ?? 10 }} caracteres,
    sin espacios.
</div>

@foreach($questions->take(2) as $index => $question)
    <div class="qa-block">
        <div class="section-title">Pregunta {{ $index + 1 }}</div>
        <label class="question-label">{{ $question->pregunta }}</label>
        <div class="wrap-input100">
            <input class="input100"
                   type="text"
                   name="respuesta_{{ $index + 1 }}"
                   id="respuesta_{{ $index + 1 }}"
                   placeholder="Respuesta {{ $index + 1 }}"
                 autocomplete="off"
                 maxlength="{{ $max_respuesta ?? 10 }}">
            <span class="focus-input100"></span>
        </div>
    </div>
@endforeach

<div class="container-login100-form-btn">
    <button type="submit" class="login100-form-btn" id="verify-submit">
        Verificar respuestas
    </button>
</div>

<div class="container-login100-form-btn">
    <a class="login100-form-btn" href="{{ url('/login') }}" style="text-decoration:none;display:flex;justify-content:center;">
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
const form = document.getElementById('verify-questions-form');
const errorBox = document.getElementById('verify-error');
const submitButton = document.getElementById('verify-submit');
const minRespuesta = {{ $min_respuesta ?? 5 }};
const maxRespuesta = {{ $max_respuesta ?? 10 }};

function blockKeyRepeat(input){
    input.addEventListener('keydown', function(e){
        if(e.repeat){
            e.preventDefault();
        }
    });
}

function showError(msg) {
    errorBox.style.display = 'block';
    errorBox.textContent = msg;
}

function clearError() {
    errorBox.style.display = 'none';
    errorBox.textContent = '';
}

['respuesta_1', 'respuesta_2'].forEach((id) => {
    const input = document.getElementById(id);
    if (!input) return;

    blockKeyRepeat(input);

    input.addEventListener('input', function () {
        let value = this.value.replace(/\s/g, '');
        if (value.length > maxRespuesta) {
            value = value.slice(0, maxRespuesta);
            showError(`Cada respuesta debe tener entre ${minRespuesta} y ${maxRespuesta} caracteres.`);
        }
        this.value = value;
    });
});

form.addEventListener('submit', (e) => {
    clearError();

    const respuesta1 = document.getElementById('respuesta_1').value.trim();
    const respuesta2 = document.getElementById('respuesta_2').value.trim();

    if (!respuesta1 || !respuesta2) {
        e.preventDefault();
        showError('Completa ambas respuestas.');
        return;
    }

    if (/\s/.test(respuesta1) || /\s/.test(respuesta2)) {
        e.preventDefault();
        showError('Las respuestas no permiten espacios en blanco.');
        return;
    }

    if (respuesta1.length < minRespuesta || respuesta1.length > maxRespuesta || respuesta2.length < minRespuesta || respuesta2.length > maxRespuesta) {
        e.preventDefault();
        showError(`Cada respuesta debe tener entre ${minRespuesta} y ${maxRespuesta} caracteres.`);
        return;
    }

    submitButton.disabled = true;
    submitButton.textContent = 'Verificando...';
});
</script>

</body>
</html>





