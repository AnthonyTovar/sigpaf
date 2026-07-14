$(document).ready(function() {

    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // SEGURIDAD Y RECARGA
    window.onpageshow = function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    };
});