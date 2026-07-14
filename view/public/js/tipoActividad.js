$(document).ready(function() {

    // ═══════════════════════════════════════════════
    // FUNCIÓN GLOBAL DE ALERTAS
    // ═══════════════════════════════════════════════
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
        alerta.stop(true, true).fadeIn(400).delay(3500).fadeOut(400);
    }

    // ═══════════════════════════════════════════════
    // MOSTRAR / LIMPIAR ERRORES
    // ═══════════════════════════════════════════════
    function mostrarError(campo, mensaje) {
        var input = $('[name="' + campo + '"]');
        var errorDiv = $('#error-' + campo);
        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).css('display', 'block');
        }
    }

    function limpiarErrores(formId) {
        $('#' + formId + ' input, #' + formId + ' textarea').removeClass('is-invalid');
        $('#' + formId + ' .invalid-feedback').text('').css('display', 'none');
    }

    // ═══════════════════════════════════════════════
    // VALIDACIONES
    // ═══════════════════════════════════════════════
    function validarFormNuevo() {
        limpiarErrores('formNuevoTipoActividad');
        let esValido = true;
        
        const nombre = $('input[name="nomTipoActividad"]').val().trim();
        const descripcion = $('textarea[name="descTipoActividad"]').val().trim();

        if (nombre === '') {
            mostrarError('nomTipoActividad', 'El nombre del tipo es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nomTipoActividad', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        }

        if (descripcion === '') {
            mostrarError('descTipoActividad', 'La descripción es obligatoria.');
            esValido = false;
        }

        return esValido;
    }

    function validarFormEditar() {
        limpiarErrores('formEditarTipoActividad');
        let esValido = true;

        const nombre = $('#nomTipoActividadEdit').val().trim();
        const descripcion = $('#descTipoActividadEdit').val().trim();

        if (nombre === '') {
            mostrarError('nomTipoActividadEdit', 'El nombre del tipo es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nomTipoActividadEdit', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        }

        if (descripcion === '') {
            mostrarError('descTipoActividadEdit', 'La descripción es obligatoria.');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // EVENTOS Y AJAX
    // ═══════════════════════════════════════════════

    $('#modalTipoActividad').on('hidden.bs.modal', function() {
        $('#formNuevoTipoActividad')[0].reset();
        limpiarErrores('formNuevoTipoActividad');
    });

    $('#modalEditarTipoActividad').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarTipoActividad');
    });

    // GUARDAR NUEVO TIPO DE ACTIVIDAD
    $('#formNuevoTipoActividad').on('submit', function(e) {
        e.preventDefault(); 
        if (!validarFormNuevo()) return false;

        $.ajax({
            url: 'index.php?action=guardarTipoActividad',
            type: 'POST', 
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalTipoActividad').modal('hide');
                    $('#formNuevoTipoActividad')[0].reset();
                    lanzarAviso(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            }
        });
    });

    // CARGAR PARA EDITAR
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarTipoActividad&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idTipoActividadEdit').val(data.idTipoActividad);
                    $('#nomTipoActividadEdit').val(data.nomTipoActividad);
                    $('#descTipoActividadEdit').val(data.descTipoActividad);
                    limpiarErrores('formEditarTipoActividad');
                    $('#modalEditarTipoActividad').modal('show');
                }
            }
        });
    });

    // ACTUALIZAR TIPO DE ACTIVIDAD
    $('#formEditarTipoActividad').on('submit', function(e) {
        e.preventDefault();
        if (!validarFormEditar()) return false;

        $.ajax({
            url: 'index.php?action=editarTipoActividad',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarTipoActividad').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            }
        });
    });

    // ELIMINAR TIPO DE ACTIVIDAD
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idTipoActividad = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este tipo de actividad?')) {
            $.ajax({
                url: 'index.php?action=eliminarTipoActividad',
                type: 'POST',
                data: { idTipoActividad: idTipoActividad },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.fadeOut(600, function() {
                            $(this).remove();
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay tipos de actividad registrados actualmente.</td></tr>');
                            }
                        });
                    } else {
                        lanzarAviso(response.message, "danger");
                    }
                }
            });
        }
    });
});