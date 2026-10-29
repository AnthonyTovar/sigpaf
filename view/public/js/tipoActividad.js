// Gestión de tipos de actividad

$(document).ready(function() {


    // Función global de alertas

    function lanzarAviso(mensaje, tipo) {
    Validar.aviso(mensaje, tipo);
}


    // Mostrar / limpiar errores

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


    // Validaciones

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


    // Eventos y AJAX


    $('#modalTipoActividad').on('hidden.bs.modal', function() {
        $('#formNuevoTipoActividad')[0].reset();
        limpiarErrores('formNuevoTipoActividad');
    });

    $('#modalEditarTipoActividad').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarTipoActividad');
    });

    // Guardar nuevo tipo de actividad
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

    // Cargar para editar
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

    // Actualizar tipo de actividad
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

    // Eliminar tipo de actividad
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idTipoActividad = $(this).data('id');
        const fila = $(this).closest('tr');

        Validar.confirmar('¿Estás seguro de eliminar este tipo de actividad?', function() {
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
        });
    });
});