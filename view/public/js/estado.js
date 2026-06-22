$(document).ready(function() {

    //FUNCIÓN GLOBAL
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

    //MOSTRAR ERROR 
    function mostrarError(campo, mensaje) {
        var input = $('[name="' + campo + '"]');
        var errorDiv = $('#error-' + campo);
        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).css('display', 'block');
        }
    }

    function limpiarErrores(formId) {
        $('#' + formId + ' input').removeClass('is-invalid');
        $('#' + formId + ' .invalid-feedback').text('').css('display', 'none');
    }

    // VALIDAR NUEVO
    function validarFormNuevo() {
        limpiarErrores('formNuevoEstado');
        var esValido = true;
        var nombre = $('input[name="nombreEstado"]').val().trim();

        if (nombre === '') {
            mostrarError('nombreEstado', 'El nombre del estado es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nombreEstado', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 25) {
            mostrarError('nombreEstado', 'El nombre no puede exceder los 25 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nombreEstado', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        return esValido;
    }

    // VALIDAR EDITAR
    function validarFormEditar() {
        limpiarErrores('formEditarEstado');
        var esValido = true;
        var nombre = $('#nombreEstadoEdit').val().trim();

        if (nombre === '') {
            mostrarError('nombreEstadoEdit', 'El nombre del estado es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nombreEstadoEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 25) {
            mostrarError('nombreEstadoEdit', 'El nombre no puede exceder los 25 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nombreEstadoEdit', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        return esValido;
    }

    // LIMPIAR AL CERRAR
    $('#modalEstado').on('hidden.bs.modal', function() {
        $('#formNuevoEstado')[0].reset();
        limpiarErrores('formNuevoEstado');
    });

    $('#modalEditarEstado').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarEstado');
    });

    // GUARDAR NUEVO ESTADO
    $('#formNuevoEstado').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return false;
        }

        var $form = $(this);

        $.ajax({
            url: 'index.php?action=guardarEstado',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEstado').modal('hide');
                    $form[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="3"]').length > 0) {
                        $('table tbody').empty();
                    }

                    var nuevaFila = '<tr style="display:none;">' +
                        '<td><span class="badge bg-secondary px-2 py-1">' + response.id + '</span></td>' +
                        '<td class="fw-bold text-dark"><span>' + response.nombre + '</span></td>' +
                        '<td class="text-center">' +
                            '<div class="btn-group">' +
                                '<button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="' + response.id + '"><i class="bi bi-pencil-square"></i></button>' +
                                '<button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="' + response.id + '"><i class="bi bi-trash3-fill"></i></button>' +
                            '</div>' +
                        '</td>' +
                    '</tr>';

                    var $fila = $(nuevaFila);
                    $('table tbody').append($fila);
                    $fila.fadeIn(800);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            },
            error: function() {
                lanzarAviso("Error al procesar el registro", "danger");
            }
        });
    });

    // CARGAR DATOS EN MODAL DE EDICIÓN
    $(document).on('click', '.btn-editar', function() {
        var idEstado = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarEstado&id=' + idEstado,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idEstadoEdit').val(data.idEstado);
                    $('#nombreEstadoEdit').val(data.nombreEstado);
                    limpiarErrores('formEditarEstado');
                    $('#modalEditarEstado').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del estado.", "danger");
            }
        });
    });

    // ACTUALIZAR ESTADO
    $('#formEditarEstado').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return false;
        }

        var idActualizado = $('#idEstadoEdit').val();
        var nombreNuevo = $('#nombreEstadoEdit').val();

        $.ajax({
            url: 'index.php?action=editarEstado',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarEstado').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    var fila = $('button[data-id="' + idActualizado + '"]').closest('tr');
                    fila.find('td:nth-child(2) span').text(nombreNuevo);
                    fila.fadeOut(100).fadeIn(800);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            },
            error: function() {
                lanzarAviso("Error al actualizar el registro", "danger");
            }
        });
    });

    // ELIMINAR ESTADO
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var idEstado = $(this).data('id');
        var fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este estado?')) {
            $.ajax({
                url: 'index.php?action=eliminarEstado',
                type: 'POST',
                data: { idEstado: idEstado },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.fadeOut(600, function() {
                            $(this).remove();
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="3" class="text-center py-4 text-muted">No hay Estados.</td></tr>');
                            }
                        });
                    } else {
                        lanzarAviso(response.message, "danger");
                    }
                },
                error: function() {
                    lanzarAviso("Ocurrió un error al intentar eliminar.", "danger");
                }
            });
        }
    });

});