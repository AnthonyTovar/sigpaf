$(document).ready(function() {

    // FUNCIÓN GLOBAL
    function lanzarAviso(mensaje, tipo) {
        const alerta = $('#registro-alerta');
        const icono = $('#alerta-icono');
        const texto = $('#alerta-texto');

        alerta.removeClass('alert-success alert-danger alert-warning').addClass('alert-' + tipo);

        let iconClass = '';
        if (tipo === 'success') iconClass = 'bi-check-circle-fill';
        if (tipo === 'warning') iconClass = 'bi-pencil-square';
        if (tipo === 'danger')  iconClass = 'bi-exclamation-triangle-fill';

        icono.attr('class', 'bi ' + iconClass + ' me-2');
        texto.text(mensaje);

        alerta.fadeIn(400).delay(3500).fadeOut(400);
    }

    // FUNCIONES PARA MOSTRAR/OCULTAR ERRORES
    function mostrarError(campo, mensaje) {
        const input = $(`[name="${campo}"]`);
        const errorDiv = $(`#error-${campo}`);

        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).show();
        }
    }

    function limpiarErrores(formId) {
        $(`#${formId} input, #${formId} select, #${formId} textarea`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    // VALIDAR NOMBRE DE USUARIO
    function validarNombreUsuario(nombre) {
        return /^[a-zA-Z0-9_.-]{3,50}$/.test(nombre);
    }

    // VALIDAR CONTRASEÑA
    function validarContrasena(contrasena, requerido = true) {
        if (!requerido && contrasena === '') return true;
        return contrasena.length >= 6 && contrasena.length <= 255;
    }

    // VALIDAR FORMULARIO EDITAR PERFIL
    function validarFormPerfil() {
        let esValido = true;
        limpiarErrores('formEditarPerfil');

        const nombre = $('#nombreUsuarioPerfil').val().trim();
        const contrasena = $('#contrasenaPerfil').val();
        const confirmar = $('#confirmarContrasenaPerfil').val();

        if (nombre === '') {
            mostrarError('nombreUsuarioPerfil', 'El nombre de usuario es obligatorio.');
            esValido = false;
        } else if (!validarNombreUsuario(nombre)) {
            mostrarError('nombreUsuarioPerfil', 'Use solo letras, números, puntos, guiones y guiones bajos (3-50 caracteres).');
            esValido = false;
        }

        if (contrasena !== '') {
            if (!validarContrasena(contrasena)) {
                mostrarError('contrasenaPerfil', 'La contraseña debe tener al menos 6 caracteres.');
                esValido = false;
            } else if (contrasena !== confirmar) {
                mostrarError('confirmarContrasenaPerfil', 'Las contraseñas no coinciden.');
                esValido = false;
            }
        }

        return esValido;
    }

    // LIMPIAR ERRORES AL CERRAR MODAL
    $('#modalEditarPerfil').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarPerfil');
    });

    // ABRIR MODAL DESDE BOTÓN EDITAR EN LA TABLA
    $(document).on('click', '.btn-editar', function() {
        limpiarErrores('formEditarPerfil');
        $('#modalEditarPerfil').modal('show');
    });

    // ACTUALIZAR PERFIL
    $('#formEditarPerfil').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormPerfil()) {
            return;
        }

        const nombreNuevo = $('#nombreUsuarioPerfil').val();

        $.ajax({
            url: 'index.php?action=actualizarPerfil',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarPerfil').modal('hide');
                    lanzarAviso(response.message, 'success');

                    // Actualizar el nombre en la tabla
                    const fila = $('table tbody tr');
                    fila.find('td:nth-child(2) span').text(nombreNuevo);

                    // Actualizar el nombre en el sidebar
                    $('.sidebar-user .dropdown-toggle span').text(nombreNuevo);

                    fila.fadeOut(100).fadeIn(800);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            },
            error: function() {
                lanzarAviso("Error al actualizar el perfil", "danger");
            }
        });
    });
});