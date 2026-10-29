// Actividad de la sesión


(function () {
    'use strict';



    var INTERVALO_MS = (typeof window.SIGPAF_PING_INTERVAL === 'number' && window.SIGPAF_PING_INTERVAL > 0)
        ? window.SIGPAF_PING_INTERVAL
        : 30000;

    var redirigiendo = false;

    function redirigirAlLogin() {
        if (redirigiendo) {
            return;
        }
        redirigiendo = true;
        window.location.href = 'index.php?action=login&error=sesion_expirada';
    }

    function enviarPing() {
        if (redirigiendo) {
            return;
        }

        fetch('index.php?action=pingSesion', {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (respuesta) {
                if (!respuesta.ok) {


                    return null;
                }
                return respuesta.json();
            })
            .then(function (datos) {
                if (datos && datos.status === 'expired') {
                    redirigirAlLogin();
                }
            })
            .catch(function () {



            });
    }


    setInterval(enviarPing, INTERVALO_MS);



    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            enviarPing();
        }
    });
})();
