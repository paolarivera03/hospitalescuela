<!DOCTYPE html>
<html lang="es">
<head>
<title>Crear Usuario</title>
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

.wrap-login100{width:420px;max-width:95%;border-radius:16px;}
.login100-form{border-radius:16px;padding:44px 38px 40px;box-shadow:0 18px 45px rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.5);backdrop-filter:blur(3px);}
.login100-form-title{font-size:22px;line-height:1.4;margin-bottom:18px;color:#1f2933;}
.container-login100::before{background-color:#222c5e80;}
.wrap-input100{margin-bottom:10px;}
.input100{font-size:18px;padding-left:70px;transition:all .25s ease;}
.input100:focus{outline:none!important;border:none!important;box-shadow:none!important;}
.login100-form .focus-input100::after{color:black!important;}
.login100-form .input100:focus + .focus-input100::after{color:black!important;}
.login100-form .has-val.input100 + .focus-input100::after{color:black!important;}
.login100-form .focus-input100::before{background:#222c5e!important;height:2px;}
.login100-form .input100:focus + .focus-input100::before{background:#222c5e!important;}
.login100-form .has-val.input100 + .focus-input100::before{background:#222c5e!important;}
.login100-form .input100:focus{padding-left:60px;}
.login100-form .has-val.input100{padding-left:60px;}
.login100-form-btn{font-size:16px;text-transform:uppercase;letter-spacing:.5px;border-radius:999px;background:var(--button-color)!important;color:#ffffff!important;box-shadow:0 12px 24px var(--button-color);border:none;transition:transform .18s ease, box-shadow .18s ease;}
.login100-form-btn:hover{transform:translateY(-1px) scale(1.02);box-shadow:0 16px 30px var(--button-color);}
.login100-form-btn:active{transform:translateY(0) scale(.99);box-shadow:0 10px 20px var(--button-color);}
#register-button:disabled{opacity:.8;cursor:not-allowed;box-shadow:0 8px 16px var(--button-color);}
.container-login100-form-btn{margin-top:18px;} /* igual que login */
</style>
</head>

<body>
<div class="limiter">
    <div class="container-login100" style="background-image: url('{{ asset('login-assets/images/bg-01.jpg') }}');">
        <div class="wrap-login100 p-t-30 p-b-50">
            <form id="register-form" class="login100-form validate-form p-b-33 p-t-5">
                @csrf
                <span class="login100-form-title p-b-41">Crear usuario</span>
                <div style="text-align:center;margin-bottom:20px;">
                    <img src="{{ asset('login-assets/images/logo-circle.png') }}" width="120">
                </div>
                <div id="register-error" class="alert alert-danger mt-3" style="display:none;"></div>
                <div id="register-success" class="alert alert-success mt-3" style="display:none;"></div>

                <div class="wrap-input100 validate-input">
                    <input maxlength="50" class="input100" type="text" name="nombre" id="nombre" placeholder="Nombre">
                    <span class="focus-input100" data-placeholder="&#xe82a;"></span>
                </div>

                <div class="wrap-input100 validate-input">
                    <input maxlength="50" class="input100" type="text" name="apellido" id="apellido" placeholder="Apellido">
                    <span class="focus-input100" data-placeholder="&#xe82a;"></span>
                </div>

                <div class="wrap-input100 validate-input">
                    <input maxlength="20" class="input100" type="text" name="usuario" id="usuario" placeholder="Usuario" style="text-transform:uppercase;" pattern="[A-Z0-9]+">
                    <span class="focus-input100" data-placeholder="&#xe82a;"></span>
                </div>

                <div class="wrap-input100 validate-input">
                    <input maxlength="100" class="input100" type="email" name="correo" id="correo" placeholder="Correo electrónico">
                    <span class="focus-input100" data-placeholder="&#xe818;"></span>
                </div>

                <div class="container-login100-form-btn">
                    <button type="submit" class="login100-form-btn" id="register-button">Registrar usuario</button>
                </div>

                <div class="container-login100-form-btn">
                    <a class="login100-form-btn" href="{{ url('/login') }}" style="text-decoration:none; display:flex; justify-content:center;">Volver al Login</a>
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
// --- JS validaciones ---
const registerForm = document.getElementById('register-form');
const registerButton = document.getElementById('register-button');
const registerErrorBox = document.getElementById('register-error');
const registerSuccessBox = document.getElementById('register-success');

function showRegisterError(msg){ registerErrorBox.style.display='block'; registerErrorBox.textContent=msg; }
function clearRegisterError(){ registerErrorBox.style.display='none'; registerErrorBox.textContent=''; }
function showRegisterSuccess(msg){ registerSuccessBox.style.display='block'; registerSuccessBox.textContent=msg; }
function clearRegisterSuccess(){ registerSuccessBox.style.display='none'; registerSuccessBox.textContent=''; }

function preventHoldKey(input, onlyUpper=false, maxLen=50){
    input.addEventListener('keydown', function(e){ if(e.repeat) e.preventDefault(); });
    input.addEventListener('input', function(){
        let val=input.value;
        if(onlyUpper) val=val.toUpperCase().replace(/[^A-Z0-9]/g,'');
        if(val.length>maxLen) val=val.slice(0,maxLen);
        input.value=val;
    });
}

const nombreInput = document.getElementById('nombre');
const apellidoInput = document.getElementById('apellido');
const usuarioInput = document.getElementById('usuario');
const correoInput = document.getElementById('correo');

preventHoldKey(nombreInput,false,50);
preventHoldKey(apellidoInput,false,50);
preventHoldKey(usuarioInput,true,20);
preventHoldKey(correoInput,false,100);

function isValidEmail(email){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }

registerForm.addEventListener('submit', async function(e){
    e.preventDefault();
    clearRegisterError();
    clearRegisterSuccess();
    const nombre=nombreInput.value.trim();
    const apellido=apellidoInput.value.trim();
    const usuario=usuarioInput.value.trim();
    const correo=correoInput.value.trim();

    if(!nombre||!apellido||!usuario||!correo){ showRegisterError('Por favor, completa todos los campos obligatorios.'); return; }
    if(!isValidEmail(correo)){ showRegisterError('Correo inválido.'); return; }

    registerButton.disabled=true;
    registerButton.textContent='Registrando...';

    try{
        const response=await fetch('http://localhost:3000/api/register',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({nombre,apellido,usuario,correo})
        });
        const data=await response.json().catch(()=>({}));
        if(!response.ok){
            showRegisterError(data.message || 'No se pudo registrar el usuario.');
            registerButton.disabled=false;
            registerButton.textContent='Registrar usuario';
            return;
        }
        showRegisterSuccess(data.message || 'Usuario registrado correctamente. Se enviará una contraseña temporal al correo.');
        registerForm.reset();
    }catch(err){
        console.error(err);
        showRegisterError('Ocurrió un error al conectar con el servidor.');
    }finally{
        registerButton.disabled=false;
        registerButton.textContent='Registrar usuario';
    }
});
</script>

</body>
</html>




