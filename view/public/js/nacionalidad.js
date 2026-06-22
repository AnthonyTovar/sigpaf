$(document).ready(function() {

    // ═══════════════════════════════════════════════
    // FUNCIÓN GLOBAL DE ALERTAS
    // ═══════════════════════════════════════════════
    function lanzarAviso(mensaje, tipo) {
        var alerta = $('#registro-alerta');
        var icono = $('#alerta-icono');
        var texto = $('#alerta-texto');

        alerta.removeClass('alert-success alert-danger alert-warning').addClass('alert-' + tipo);
        
        var iconClass = '';
        if (tipo === 'success') iconClass = 'bi-check-circle-fill';
        if (tipo === 'warning') iconClass = 'bi-pencil-square';
        if (tipo === 'danger')  iconClass = 'bi-exclamation-triangle-fill';
        
        icono.attr('class', 'bi ' + iconClass + ' me-2');
        texto.text(mensaje);
        alerta.stop(true, true).fadeIn(400).delay(3500).fadeOut(400);
    }

    // ═══════════════════════════════════════════════
    // MOSTRAR / LIMPIAR ERRORES (IGUAL QUE PARROQUIA)
    // ═══════════════════════════════════════════════
    function mostrarError(campo, mensaje) {
        // Selecciona el input por su atributo 'name' y le pone el borde rojo
        var input = $('[name="' + campo + '"]');
        var errorDiv = $('#error-' + campo);
        
        input.addClass('is-invalid'); // Esto pone el borde rojo
        if (errorDiv.length) {
            errorDiv.text(mensaje).css('display', 'block'); // Esto muestra el texto rojo
        }
    }

    function limpiarErrores(formId) {
        $('#' + formId + ' input').removeClass('is-invalid');
        $('#' + formId + ' .invalid-feedback').text('').css('display', 'none');
    }

    // ═══════════════════════════════════════════════
    // VALIDAR FORMULARIO NUEVO
    // ═══════════════════════════════════════════════
    function validarFormNuevo() {
        limpiarErrores('formNuevaNacionalidad');
        var esValido = true;
        var nombre = $('input[name="nomNacionalidad"]').val().trim();

        if (nombre === '') {
            mostrarError('nomNacionalidad', 'El nombre de la nacionalidad es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomNacionalidad', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nomNacionalidad', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // VALIDAR FORMULARIO EDITAR
    // ═══════════════════════════════════════════════
    function validarFormEditar() {
        limpiarErrores('formEditarNacionalidad');
        var esValido = true;
        var nombre = $('#nomNacionalidadEdit').val().trim();

        if (nombre === '') {
            mostrarError('nomNacionalidadEdit', 'El nombre de la nacionalidad es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomNacionalidadEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nomNacionalidadEdit', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // MODALES Y GUARDADO (MISMA ESTRUCTURA)
    // ═══════════════════════════════════════════════
    $('#modalNacionalidad').on('hidden.bs.modal', function() {
        $('#formNuevaNacionalidad')[0].reset();
        limpiarErrores('formNuevaNacionalidad');
    });

    $('#modalEditarNacionalidad').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarNacionalidad');
    });

    $('#formNuevaNacionalidad').on('submit', function(e) {
        e.preventDefault();
        if (!validarFormNuevo()) return false;

        var $form = $(this);
        $.ajax({
            url: 'index.php?action=guardarNacionalidad',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalNacionalidad').modal('hide');
                    $form[0].reset();
                    lanzarAviso(response.message, 'success');
                    location.reload(); // O tu lógica de insertar fila
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            }
        });
    });

    $(document).on('click', '.btn-editar', function() {
        var id = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarNacionalidad&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idNacionalidadEdit').val(data.idNacionalidad);
                    $('#nomNacionalidadEdit').val(data.nomNacionalidad);
                    limpiarErrores('formEditarNacionalidad');
                    $('#modalEditarNacionalidad').modal('show');
                }
            }
        });
    });

    $('#formEditarNacionalidad').on('submit', function(e) {
        e.preventDefault();
        if (!validarFormEditar()) return false;

        $.ajax({
            url: 'index.php?action=editarNacionalidad',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarNacionalidad').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            }
        });
    });

    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var fila = $(this).closest('tr');
        if (confirm('¿Seguro?')) {
            $.ajax({
                url: 'index.php?action=eliminarNacionalidad',
                type: 'POST',
                data: { idNacionalidad: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.fadeOut(600, function() { $(this).remove(); });
                    }
                }
            });
        }
    });
});