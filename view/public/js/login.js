// Gestión de inicio de sesión

$(document).ready(function() {
    const uInput = $('#username-input');
    const pInput = $('#password-input');
    const uArea  = $('#user-display-area'); 
    const uWrap  = $('.username-wrap');
    const loginBox = $('.login');
    const errorMsg = $('#msg-error');
    const loginForm = $('#loginForm');
    const btnSubmit = $('#btn-submit-custom');

    const togglePw = $('#toggle-password');
    const eyeIcon = $('#eye-icon');

    togglePw.on('click', function(e) {
        e.preventDefault();
        const tipoActual = pInput.attr('type');
        if (tipoActual === 'password') {
            pInput.attr('type', 'text');
            eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
            togglePw.attr('title', 'Ocultar contraseña');
        } else {
            pInput.attr('type', 'password');
            eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
            togglePw.attr('title', 'Mostrar contraseña');
        }
    });


    function mostrarError(texto, elemento) {
        errorMsg.text(texto).stop().hide().fadeIn(400); 
        elemento.addClass('shake');
        

        setTimeout(() => { 
            errorMsg.fadeOut(600); 
            elemento.removeClass('shake');
        }, 2000);
    }

    function resetearUsuario() {
        loginBox.removeClass('pw-active');
        togglePw.addClass('hidden').show();

        uArea.fadeOut(200, function() {
            $(this).addClass('hidden');

            setTimeout(function() {
            
                uWrap.removeClass('fantasma').css({'display': 'block', 'opacity': '0'});

                
                void uWrap[0].offsetWidth; 

            
                uWrap.addClass('visible-seda'); 
                
                uInput.removeAttr('readonly').val('').focus();
                pInput.val('');
                errorMsg.hide();
            }, 400); 
        });
    }

    
    function irAPassword() {
        const val = uInput.val().trim();
        if (val === "") {
            mostrarError("INGRESA TU USUARIO", uInput);
            return;
        }

        uInput.attr('readonly', true);
        $('#name').text(val);
        

        uWrap.fadeOut(250, function() {
            
            $(this).addClass('fantasma').removeClass('visible-seda').hide();
            
            
            uArea.hide().removeClass('hidden').fadeIn(350);
            

            setTimeout(() => {
                loginBox.addClass('pw-active');
                togglePw.removeClass('hidden').hide().fadeIn(300);
                setTimeout(() => pInput.focus(), 400);
            }, 100);
        });
    }

    // Eventos con propagación controlada

    $('.edit-user, #user-display-area').on('click', function(e) {
        e.preventDefault();
        resetearUsuario();
    });

    btnSubmit.on('click', function(e) {
        e.preventDefault();

        if (!loginBox.hasClass('pw-active')) {
            irAPassword();
        } else {
            if (pInput.val().trim() === "") {
                mostrarError("INGRESA TU CLAVE", pInput);
            } else {
                enviarLogin(false);
            }
        }
    });

    // Envía el formulario de inicio de sesión por AJAX.
    // Si forzarCierre es true, indica al servidor que cierre la sesión activa previa.
    function enviarLogin(forzarCierre) {
        btnSubmit.css('opacity', '0.7').text("VALIDANDO...").prop('disabled', true);

        uInput.prop('readonly', false);

        let datos = loginForm.serialize();
        if (forzarCierre) {
            datos += '&forzar_cierre=1';
        }

        $.ajax({
            url: 'index.php?action=login',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('.auth-container').fadeOut(600, function() {
                        window.location.href = response.redirect;
                    });
                } else if (response.status === 'sesion_activa') {
                    mostrarModalSesionActiva(response.detalle);
                    btnSubmit.css('opacity', '1').text("Iniciar Sesión").prop('disabled', false);
                    uInput.prop('readonly', true);
                } else {

                    uInput.prop('readonly', true);


                    mostrarError(response.message, pInput);


                    btnSubmit.css('opacity', '1').text("Iniciar Sesión").prop('disabled', false);
                }
            },
            error: function() {
                uInput.prop('readonly', true);
                mostrarError("ERROR DE CONEXIÓN", pInput);
                btnSubmit.css('opacity', '1').text("Iniciar Sesión").prop('disabled', false);
            }
        });
    }

    // Muestra el modal de confirmación cuando existe una sesión activa en otro dispositivo
    function mostrarModalSesionActiva(detalle) {
        const modalEl = document.getElementById('modalSesionActiva');

        // El contenedor del login usa backdrop-filter, lo que atrapa el modal
        // detrás del fondo oscuro; se mueve al body para que quede por encima
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (detalle) {
            let texto = '';
            if (detalle.inicio) {
                texto += 'Iniciada: ' + detalle.inicio + '. ';
            }
            if (detalle.ip) {
                texto += 'IP: ' + detalle.ip;
            }
            $('#modal-sesion-detalle').text(texto);
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    $('#btn-sesion-si').on('click', function() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalSesionActiva'));
        modal.hide();
        enviarLogin(true);
    });

    $('#btn-sesion-no').on('click', function() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalSesionActiva'));
        modal.hide();
        pInput.val('').focus();
    });

    uInput.on('keypress', function(e) { if(e.which == 13) { e.preventDefault(); irAPassword(); }});
    pInput.on('keypress', function(e) { if(e.which == 13) { e.preventDefault(); btnSubmit.click(); }});
});