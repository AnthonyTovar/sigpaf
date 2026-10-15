/**
 * HEARTBEAT (LATIDO) DE SESIÓN — SIGPAF
 *
 * Mientras el usuario tiene el sistema abierto en el navegador, este script
 * envía un "ping" al servidor cada X segundos (window.SIGPAF_PING_INTERVAL,
 * definido en el Layout desde SessionManager::INTERVALO_PING_SEGUNDOS).
 *
 * Gracias a esto el servidor sabe que el navegador sigue abierto:
 *  - Si el usuario cierra la pestaña o el navegador con la "X", los pings
 *    dejan de llegar y, pasados SEGUNDOS_INACTIVIDAD_MAX segundos, el
 *    servidor considera la sesión cerrada y libera el acceso.
 *  - Si el servidor responde que la sesión expiró, el navegador redirige
 *    automáticamente al login.
 *
 * No usa beforeunload/pagehide a propósito: esos eventos también se disparan
 * al navegar entre páginas del sistema y cerrarían la sesión por error.
 */
(function () {
    'use strict';

    // Intervalo del latido en milisegundos. El Layout lo define desde PHP;
    // si no existe, se usa el valor por defecto (30 s).
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
                    // Error de red o del servidor: no cerrar la sesión por esto,
                    // el siguiente intento lo reintentará.
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
                // Sin conexión momentánea (red caída, equipo suspendido, etc.):
                // se ignora; el servidor da margen de varios pings perdidos
                // (SEGUNDOS_INACTIVIDAD_MAX) antes de cerrar la sesión.
            });
    }

    // Latido periódico.
    setInterval(enviarPing, INTERVALO_MS);

    // Al volver a la pestaña (los navegadores frenan los timers en segundo
    // plano), se envía un ping de inmediato para recuperar el ritmo.
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            enviarPing();
        }
    });
})();
