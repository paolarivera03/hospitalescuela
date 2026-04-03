<!DOCTYPE html>
<html lang="es">
<head>
<title>Recuperación por preguntas</title>
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
.wrap-login100{width:420px;max-width:95%;border-radius:16px;}
.login100-form{border-radius:16px;padding:44px 38px 40px;box-shadow:0 18px 45px rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.5);backdrop-filter:blur(3px);}
.login100-form-title{font-size:22px;line-height:1.4;margin-bottom:18px;color:#1f2933;}
.container-login100::before{background-color:#222c5e80;}
.login100-form-btn{font-size:16px;text-transform:uppercase;letter-spacing:.5px;border-radius:999px;background:var(--button-color)!important;color:#ffffff!important;box-shadow:0 12px 24px var(--button-color);border:none;transition:transform .18s ease, box-shadow .18s ease;}
.login100-form-btn:hover{transform:translateY(-1px) scale(1.02);box-shadow:0 16px 30px var(--button-color);}
.container-login100-form-btn{margin-top:18px;}
.login-logo-container{margin-bottom:24px;text-align:center;}
.panel{background:rgba(255,255,255,.78);border-radius:14px;padding:12px 12px 8px;border:1px solid rgba(34,44,94,.14);margin-top:12px;}
.panel-title{font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--panel-color);margin-bottom:10px;}
.helper{font-size:13px;color:#4a5568;line-height:1.5;}
.form-label{display:block;font-size:13px;font-weight:700;color:#334;margin-bottom:6px;}
.form-control,.form-select{border-radius:12px;min-height:46px;border:1px solid rgba(34,44,94,.20);}
.form-control:disabled,.form-select:disabled{background:#eef2ff;opacity:1;}
.answer-ok{font-size:12px;color:#0a4898;font-weight:700;margin-top:6px;}
.section-spacer{margin-top:16px;}
.password-wrap{position:relative;}
.togglePassword{position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#6b7280;font-size:16px;}
</style>
</head>
<body>
<div class="limiter">
<div class="container-login100" style="background-image:url('{{ asset('login-assets/images/bg-01.jpg') }}');">
<div class="wrap-login100 p-t-30 p-b-50">
<div class="login100-form">
<span class="login100-form-title p-b-10">Recuperación por preguntas</span>
<div class="login-logo-container">
<img src="{{ asset('login-assets/images/logo-circle.png') }}" width="120" height="120" style="border-radius:50%;object-fit:cover;">
</div>

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:12px;">{{ $errors->first() }}</div>
@endif

@if(session('status'))
<div class="alert alert-success" style="margin-bottom:12px;">{{ session('status') }}</div>
@endif

<div id="recovery-error" class="alert alert-danger" style="display:none; margin-bottom:12px;"></div>

<div class="panel">
<div class="panel-title">Usuario</div>
<div class="helper"><strong>{{ $usuario }}</strong></div>
</div>

<div class="panel section-spacer">
<div class="panel-title">Preguntas validadas</div>
@if($answeredQuestions->isEmpty())
<div class="helper">Todavía no has validado ninguna pregunta.</div>
@else
@foreach($answeredQuestions as $answered)
<div class="section-spacer">
<label class="form-label">Pregunta</label>
<select class="form-select" disabled>
<option>{{ $answered->pregunta }}</option>
</select>
<label class="form-label section-spacer">Respuesta</label>
<input class="form-control" type="text" value="Respuesta validada" disabled>
<div class="answer-ok">Validada correctamente</div>
</div>
@endforeach
@endif
</div>

@if(! $questionsVerified)
<div class="panel section-spacer">
<div class="panel-title">Validar pregunta secreta</div>
<div class="helper">Selecciona una de tus preguntas configuradas y responde correctamente para continuar.</div>
<div class="helper" style="margin-top: 8px;">
La respuesta debe tener entre {{ $min_respuesta ?? 5 }} y {{ $max_respuesta ?? 10 }} caracteres,
sin espacios.
</div>
<form id="question-form" method="POST" action="{{ route('recuperacion.preguntas.validar') }}" class="section-spacer">
@csrf
<label class="form-label">Pregunta</label>
<select class="form-select" name="pregunta_id" id="pregunta_id" required>
<option value="">Selecciona una pregunta</option>
@foreach($availableQuestions as $question)
<option value="{{ $question->pregunta_id }}">{{ $question->pregunta }}</option>
@endforeach
</select>
<label class="form-label section-spacer">Respuesta</label>
<input class="form-control" type="text" name="respuesta" id="respuesta" maxlength="{{ $max_respuesta ?? 10 }}" autocomplete="off" required>
<div class="container-login100-form-btn">
<button type="submit" class="login100-form-btn">Validar pregunta</button>
</div>
</form>
</div>
@endif

@if($questionsVerified)
<div class="panel section-spacer">
<div class="panel-title">Cambiar contraseña</div>
<div class="helper">Las preguntas ya fueron validadas. Ahora ingresa tu nueva contraseña.</div>
<form id="password-form" method="POST" action="{{ route('recuperacion.preguntas.password') }}" class="section-spacer">
@csrf
<label class="form-label">Nueva contraseña</label>
<div class="password-wrap">
<input class="form-control" type="password" name="contrasena_nueva" id="contrasena_nueva" maxlength="{{ $max_contrasena ?? 10 }}" minlength="{{ $min_contrasena ?? 5 }}" autocomplete="off" required>
<span class="togglePassword" data-target="contrasena_nueva" aria-label="Mostrar u ocultar contraseña"><i class="fa fa-eye"></i></span>
</div>
<label class="form-label section-spacer">Confirmar contraseña</label>
<div class="password-wrap">
<input class="form-control" type="password" name="confirmar_contrasena" id="confirmar_contrasena" maxlength="{{ $max_contrasena ?? 10 }}" minlength="{{ $min_contrasena ?? 5 }}" autocomplete="off" required>
<span class="togglePassword" data-target="confirmar_contrasena" aria-label="Mostrar u ocultar contraseña"><i class="fa fa-eye"></i></span>
</div>
<div class="container-login100-form-btn">
<button type="submit" class="login100-form-btn">Guardar nueva contraseña</button>
</div>
</form>
</div>
@endif

<div class="container-login100-form-btn">
<a class="login100-form-btn" href="{{ route('recuperacion.opciones') }}" style="display:flex;justify-content:center;align-items:center;text-decoration:none;">Volver a opciones</a>
</div>
</div>
</div>
</div>
</div>

<script>
const questionForm = document.getElementById('question-form');
const passwordForm = document.getElementById('password-form');
const recoveryErrorBox = document.getElementById('recovery-error');
const minRespuesta = {{ $min_respuesta ?? 5 }};
const maxRespuesta = {{ $max_respuesta ?? 10 }};

function showRecoveryError(msg){
    recoveryErrorBox.style.display = 'block';
    recoveryErrorBox.textContent = msg;
}

function clearRecoveryError(){
    recoveryErrorBox.style.display = 'none';
    recoveryErrorBox.textContent = '';
}

function blockKeyRepeat(input){
    input.addEventListener('keydown', function(e){
        if(e.repeat){
            e.preventDefault();
        }
    });
}

if (questionForm) {
    const answerInput = document.getElementById('respuesta');
    if (answerInput) {
        blockKeyRepeat(answerInput);
        answerInput.addEventListener('input', function () {
            let value = this.value.replace(/\s/g, '');
            if (value.length > maxRespuesta) {
                value = value.slice(0, maxRespuesta);
                showRecoveryError(`La respuesta debe tener entre ${minRespuesta} y ${maxRespuesta} caracteres.`);
            }
            this.value = value;
        });
    }

    questionForm.addEventListener('submit', function (event) {
        clearRecoveryError();
        const questionId = document.getElementById('pregunta_id').value;
        const answerInput = document.getElementById('respuesta');
        const answer = answerInput.value.trim();

        if (!questionId) {
            event.preventDefault();
            showRecoveryError('Selecciona una pregunta.');
            return;
        }

        if (!answer) {
            event.preventDefault();
            showRecoveryError('Debes ingresar una respuesta.');
            return;
        }

        if (/\s/.test(answer)) {
            event.preventDefault();
            showRecoveryError('La respuesta no permite espacios en blanco.');
            return;
        }

        if (answer.length < minRespuesta || answer.length > maxRespuesta) {
            event.preventDefault();
            showRecoveryError(`La respuesta debe tener entre ${minRespuesta} y ${maxRespuesta} caracteres.`);
            return;
        }

        answerInput.value = answer;
    });
}

if (passwordForm) {
    const newPasswordInput = document.getElementById('contrasena_nueva');
    const confirmPasswordInput = document.getElementById('confirmar_contrasena');
    const minPassword = {{ $min_contrasena ?? 5 }};
    const maxPassword = {{ $max_contrasena ?? 10 }};
    const robustPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).+$/;
    if (newPasswordInput) blockKeyRepeat(newPasswordInput);
    if (confirmPasswordInput) blockKeyRepeat(confirmPasswordInput);

    function isControlKey(event) {
        return event.ctrlKey || event.metaKey || event.altKey || ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'].includes(event.key);
    }

    function normalizePasswordInput(inputEl) {
        let value = inputEl.value.replace(/\s/g, '');
        if (value.length > maxPassword) {
            value = value.slice(0, maxPassword);
            showRecoveryError(`La contraseña debe tener entre ${minPassword} y ${maxPassword} caracteres.`);
        }
        inputEl.value = value;
        return value;
    }

    function validatePasswordOnAttempt() {
        const newPassword = normalizePasswordInput(newPasswordInput);
        const confirmPassword = normalizePasswordInput(confirmPasswordInput);

        if (!newPassword && !confirmPassword) {
            clearRecoveryError();
            return;
        }

        if (newPassword.length < minPassword || newPassword.length > maxPassword) {
            showRecoveryError(`La contraseña debe tener entre ${minPassword} y ${maxPassword} caracteres.`);
            return;
        }

        if (!robustPassword.test(newPassword)) {
            showRecoveryError('La contraseña debe contener mayúscula, minúscula, número y carácter especial.');
            return;
        }

        if (confirmPassword && newPassword !== confirmPassword) {
            showRecoveryError('Las contraseñas no coinciden.');
            return;
        }

        clearRecoveryError();
    }

    [newPasswordInput, confirmPasswordInput].forEach(function (input) {
        if (!input) return;

        input.addEventListener('keydown', function (event) {
            if (!isControlKey(event) && this.value.length >= maxPassword) {
                event.preventDefault();
                showRecoveryError(`La contraseña debe tener entre ${minPassword} y ${maxPassword} caracteres.`);
            }
        });

        input.addEventListener('input', function () {
            normalizePasswordInput(this);
            clearRecoveryError();
        });

        input.addEventListener('blur', validatePasswordOnAttempt);
    });

    document.querySelectorAll('.togglePassword').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            const newType = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', newType);

            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    });

    passwordForm.addEventListener('submit', function (event) {
        clearRecoveryError();
        const newPassword = document.getElementById('contrasena_nueva').value;
        const confirmPassword = document.getElementById('confirmar_contrasena').value;

        if (/\s/.test(newPassword) || /\s/.test(confirmPassword)) {
            event.preventDefault();
            showRecoveryError('La contraseña no debe contener espacios en blanco.');
            return;
        }

        if (newPassword.length < minPassword || newPassword.length > maxPassword) {
            event.preventDefault();
            showRecoveryError(`La contraseña debe tener entre ${minPassword} y ${maxPassword} caracteres.`);
            return;
        }

        if (!robustPassword.test(newPassword)) {
            event.preventDefault();
            showRecoveryError('La contraseña debe contener mayúscula, minúscula, número y carácter especial.');
            return;
        }

        if (newPassword !== confirmPassword) {
            event.preventDefault();
            showRecoveryError('Las contraseñas no coinciden.');
        }
    });
}
</script>
<script src="{{ asset('login-assets/js/unsaved-guard.js') }}"></script>
</body>
</html>
