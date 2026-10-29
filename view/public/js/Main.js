// Funciones principales

// Control de letras repetidas
$(document).on('input keyup paste', 'input[type="text"], input[type="search"], textarea', function() {
    const $this = $(this);
    setTimeout(function() {
        let val = $this.val();
        

        const regexLetrasRepetidas = /([a-zA-ZáéíóúÁÉÍÓÚñÑ])\1{2,}/gi;

        if (regexLetrasRepetidas.test(val)) {

            $this.addClass('vibrar-input');
            setTimeout(() => $this.removeClass('vibrar-input'), 300);


            let $contenedorPadre = $this.closest('.input-group');
            if ($contenedorPadre.length === 0) {
                $contenedorPadre = $this;
            }


            let $msgError = $contenedorPadre.next('.msj-repetidos-temp');

            if ($msgError.length === 0) {
                $msgError = $(
                    '<div class="msj-repetidos-temp">' +
                        '<i class="bi bi-exclamation-circle-fill"></i>' +
                        '<span>No puede haber más de tres letras iguales seguidas.</span>' +
                    '</div>'
                );

                $contenedorPadre.after($msgError);
            }


            clearTimeout($this.data('timerError'));
            let timer = setTimeout(function() {
                $msgError.addClass('ocultando');
                setTimeout(() => $msgError.remove(), 300);
            }, 3000);
            $this.data('timerError', timer);


            val = val.replace(regexLetrasRepetidas, '$1$1$1');
            $this.val(val);
        }
    }, 0);
});




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


    if (!esMovil() && localStorage.getItem(STORAGE_KEY) === 'expanded') {
        $('body').addClass('sidebar-expanded');
    }

    $(document).on('click', '#btn-sidebar-toggle', function (e) {
        e.preventDefault();
        if (esMovil()) {

            $sidebar.toggleClass('mobile-open');
            $overlay.toggleClass('show');
        } else {

            $('body').toggleClass('sidebar-expanded');
            localStorage.setItem(
                STORAGE_KEY,
                $('body').hasClass('sidebar-expanded') ? 'expanded' : 'collapsed'
            );
        }
    });


    $(document).on('click', '#sidebar-overlay', cerrarDrawer);
    $(document).on('click', '.sidebar .nav-link', function () {
        if (esMovil()) {
            cerrarDrawer();
        }
    });


    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarDrawer();
        }
    });


    $(window).on('resize', function () {
        if (!esMovil()) {
            cerrarDrawer();
        }
    });
});