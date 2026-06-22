$(document).ready(function() {

    //FUNCIÓN GLOBA
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

    //FUNCIONES PARA MOSTRAR/OCULTAR ERRORES
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

    //VALIDACIONES DEL FORMULARIO NUEVO
    function validarFormNuevo() {
        var esValido = true;
        limpiarErrores('formNuevoEstatus');

        var nombre = $('input[name="nomEstatus"]').val().trim();
        var descripcion = $('textarea[name="descEstatus"]').val().trim();

        // Validar nombre
        if (nombre === '') {
            mostrarError('nomEstatus', 'El nombre del estatus es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomEstatus', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nomEstatus', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nomEstatus', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        //Validar descripción
        if (descripcion === '') {
            mostrarError('descEstatus', 'La descripción es obligatoria.');
            esValido = false;
        } else if (descripcion.length < 5) {
            mostrarError('descEstatus', 'La descripción debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (descripcion.length > 250) {
            mostrarError('descEstatus', 'La descripción no puede exceder los 250 caracteres.');
            esValido = false;
        }

        return esValido;
    }

    //VALIDACIONES 
    function validarFormEditar() {
        var esValido = true;
        limpiarErrores('formEditarEstatus');

        var nombre = $('#nomEstatusEdit').val().trim();
        var descripcion = $('#descEstatusEdit').val().trim();

        // Validar nombre
        if (nombre === '') {
            mostrarError('nomEstatusEdit', 'El nombre del estatus es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomEstatusEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nomEstatusEdit', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nomEstatusEdit', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        
        if (descripcion === '') {
            mostrarError('descEstatusEdit', 'La descripción es obligatoria.');
            esValido = false;
        } else if (descripcion.length < 5) {
            mostrarError('descEstatusEdit', 'La descripción debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (descripcion.length > 250) {
            mostrarError('descEstatusEdit', 'La descripción no puede exceder los 250 caracteres.');
            esValido = false;
        }

        return esValido;
    }

    //LIMPIAR ERRORES
    $('#modalEstatus').on('hidden.bs.modal', function() {
        $('#formNuevoEstatus')[0].reset();
        limpiarErrores('formNuevoEstatus');
    });

    $('#modalEditarEstatus').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarEstatus');
    });

    //GUARDAR NUEVO ESTATUS
    $('#formNuevoEstatus').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return false;
        }

        $.ajax({
            url: 'index.php?action=guardarEstatus',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEstatus').modal('hide');
                    $('#formNuevoEstatus')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="4"]').length > 0) {
                        $('table tbody tr td[colspan="4"]').closest('tr').remove();
                    }

                    var nuevaFila = '<tr style="display:none;">' +
                        '<td><span class="badge bg-secondary px-2 py-1">' + response.id + '</span></td>' +
                        '<td><span class="fw-bold text-dark">' + response.nombre + '</span></td>' +
                        '<td><span class="text-muted small">' + response.descripcion + '</span></td>' +
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

    //CARGAR DATOS EN MODAL DE EDICIÓN
    $(document).on('click', '.btn-editar', function() {
        var idEstatus = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarEstatus&id=' + idEstatus,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idEstatusEdit').val(data.idEstatus);
                    $('#nomEstatusEdit').val(data.nomEstatus);
                    $('#descEstatusEdit').val(data.descEstatus);
                    limpiarErrores('formEditarEstatus');
                    $('#modalEditarEstatus').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del estatus.", "danger");
            }
        });
    });

    //ACTUALIZAR ESTATUS
    $('#formEditarEstatus').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return false;
        }

        var idActualizado = $('#idEstatusEdit').val();
        var nuevoNombre = $('#nomEstatusEdit').val();
        var nuevaDesc = $('#descEstatusEdit').val();

        $.ajax({
            url: 'index.php?action=editarEstatus',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarEstatus').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    var fila = $('.btn-editar[data-id="' + idActualizado + '"]').closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNombre);
                    fila.find('td:nth-child(3) span').text(nuevaDesc);

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

    //ELIMINAR ESTATUS
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var idEstatus = $(this).data('id');
        var fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este estatus?')) {
            $.ajax({
                url: 'index.php?action=eliminarEstatus',
                type: 'POST',
                data: { idEstatus: idEstatus },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.fadeOut(600, function() {
                            $(this).remove();
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay estatus registrados actualmente.</td></tr>');
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