$(document).ready(function() {
    const uInput = $('#username-input');
    const pInput = $('#password-input');
    const uArea  = $('#user-display-area'); 
    const uWrap  = $('.username-wrap');
    const loginBox = $('.login');
    const errorMsg = $('#msg-error');
    const loginForm = $('#loginForm');
    const btnSubmit = $('#btn-submit-custom');
    // Toggle mostrar/ocultar contraseña
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

    // Función de error sutil
    function mostrarError(texto, elemento) {
        errorMsg.text(texto).stop().hide().fadeIn(400); 
        elemento.addClass('shake');
        
        // Desvanecimiento
        setTimeout(() => { 
            errorMsg.fadeOut(600); 
            elemento.removeClass('shake');
        }, 2000);
    }

    function resetearUsuario() {
        loginBox.removeClass('pw-active');
        // Ocultar el ojito al volver al usuario
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
        
        //Transición de salida
        uWrap.fadeOut(250, function() {
            
            $(this).addClass('fantasma').removeClass('visible-seda').hide();
            
            
            uArea.hide().removeClass('hidden').fadeIn(350);
            
            // La tapa se abre
            setTimeout(() => {
                loginBox.addClass('pw-active');
                // Mostrar el ojito cuando la tapa se abre
                togglePw.removeClass('hidden').hide().fadeIn(300);
                // El focus ocurre cuando la tapa termina de abrirse
                setTimeout(() => pInput.focus(), 400);
            }, 100);
        });
    }

    //EVENTOS CON PROPAGACIÓN CONTROLADA

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
                //INICIO AJAX INTEGRADO
                
                //Efecto visual en el botón
                btnSubmit.css('opacity', '0.7').text("VALIDANDO...").prop('disabled', true);
                
                //IMPORTANTE: Habilitamos temporalmente 
                uInput.prop('readonly', false);

                $.ajax({
                    url: 'index.php?action=login',
                    type: 'POST',
                    data: loginForm.serialize(),
                    dataType: 'json',
                    success: function(response) {
                    if (response.status === 'success') {
                    //Desvanecemos todo el contenedor de login con suavidad
                    $('.auth-container').fadeOut(600, function() {
                  
                    window.location.href = response.redirect;
                    });
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
        }
    });

    uInput.on('keypress', function(e) { if(e.which == 13) { e.preventDefault(); irAPassword(); }});
    pInput.on('keypress', function(e) { if(e.which == 13) { e.preventDefault(); btnSubmit.click(); }});
});