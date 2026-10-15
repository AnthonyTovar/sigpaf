// CONTROL GLOBAL DE LETRAS REPETIDAS, VIBRACIÓN Y MENSAJE
$(document).on('input keyup paste', 'input[type="text"], input[type="search"], textarea', function() {
    const $this = $(this);
    setTimeout(function() {
        let val = $this.val();
        
        // Regex exclusiva para LETRAS
        const regexLetrasRepetidas = /([a-zA-ZáéíóúÁÉÍÓÚñÑ])\1{2,}/gi;

        if (regexLetrasRepetidas.test(val)) {
            // 1. Activar animación de vibración
            $this.addClass('vibrar-input');
            setTimeout(() => $this.removeClass('vibrar-input'), 300);

            // 2. Determinar el contenedor correcto (Fuera del .input-group)
            let $contenedorPadre = $this.closest('.input-group');
            if ($contenedorPadre.length === 0) {
                $contenedorPadre = $this;
            }

            // Buscar si ya existe el mensaje justo después del elemento/contenedor
            let $msgError = $contenedorPadre.next('.msj-repetidos-temp');

            if ($msgError.length === 0) {
                $msgError = $(
                    '<div class="msj-repetidos-temp">' +
                        '<i class="bi bi-exclamation-circle-fill"></i>' +
                        '<span>No puede haber más de tres letras iguales seguidas.</span>' +
                    '</div>'
                );
                // insertAfter garantiza que quede inmediatamente DEBAJO de todo el input o input-group
                $contenedorPadre.after($msgError);
            }

            // Ocultar suavemente a los 3 segundos
            clearTimeout($this.data('timerError'));
            let timer = setTimeout(function() {
                $msgError.addClass('ocultando');
                setTimeout(() => $msgError.remove(), 300);
            }, 3000);
            $this.data('timerError', timer);

            // 3. Recortar ráfaga de letras repetidas
            while (regexLetrasRepetidas.test(val)) {
                val = val.replace(regexLetrasRepetidas, '$1$1');
            }
            $this.val(val);
        }
    }, 0);
});

// ==========================================
// SIDEBAR: EXPANDIR/COLAPSAR (escritorio) + DRAWER (móvil)
// ==========================================
$(function () {
    'use strict';

    var $sidebar = $('#sidebar');
    var $overlay = $('#sidebar-overlay');
    var MOBILE_BREAKPOINT = 768;
    var STORAGE_KEY = 'sigpaf_sidebar';

    function esMovil() {
        return window.innerWidth <= MOBILE_BREAKPOINT;
    }

    function cerrarDrawer() {
        $sidebar.removeClass('mobile-open');
        $overlay.removeClass('show');
    }

    // En escritorio se recuerda la preferencia del usuario
    if (!esMovil() && localStorage.getItem(STORAGE_KEY) === 'expanded') {
        $('body').addClass('sidebar-expanded');
    }

    $(document).on('click', '#btn-sidebar-toggle', function (e) {
        e.preventDefault();
        if (esMovil()) {
            // Móvil: abre/cierra el drawer con overlay
            $sidebar.toggleClass('mobile-open');
            $overlay.toggleClass('show');
        } else {
            // Escritorio: expande/colapsa y recuerda la preferencia
            $('body').toggleClass('sidebar-expanded');
            localStorage.setItem(
                STORAGE_KEY,
                $('body').hasClass('sidebar-expanded') ? 'expanded' : 'collapsed'
            );
        }
    });

    // Cerrar el drawer al tocar el overlay o al elegir una opción del menú
    $(document).on('click', '#sidebar-overlay', cerrarDrawer);
    $(document).on('click', '.sidebar .nav-link', function () {
        if (esMovil()) {
            cerrarDrawer();
        }
    });

    // Tecla Escape cierra el drawer
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarDrawer();
        }
    });

    // Al pasar de móvil a escritorio se limpia el estado del drawer
    $(window).on('resize', function () {
        if (!esMovil()) {
            cerrarDrawer();
        }
    });
});