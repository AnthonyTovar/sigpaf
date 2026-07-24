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