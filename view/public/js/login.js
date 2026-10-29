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
                // Inicio AJAX integrado
                
                btnSubmit.css('opacity', '0.7').text("VALIDANDO...").prop('disabled', true);
                
                uInput.prop('readonly', false);

                $.ajax({
                    url: 'index.php?action=login',
                    type: 'POST',
                    data: loginForm.serialize(),
                    dataType: 'json',
                    success: function(response) {
                    if (response.status === 'success') {
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