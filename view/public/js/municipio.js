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
    // MOSTRAR / LIMPIAR ERRORES
    // ═══════════════════════════════════════════════
    function mostrarError(campo, mensaje) {
        var input = $('[name="' + campo + '"]');
        var errorDiv = $('#error-' + campo);
        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).show();
        }
    }

    function limpiarErrores(formId) {
        $('#' + formId + ' .is-invalid').removeClass('is-invalid');
        $('#' + formId + ' .invalid-feedback').text('').hide();
    }

    // ═══════════════════════════════════════════════
    // VALIDAR FORMULARIO NUEVO
    // ═══════════════════════════════════════════════
    function validarFormNuevo() {
        limpiarErrores('formNuevoMunicipio');
        var esValido = true;
        var nombre = $('input[name="nombreMunicipio"]').val().trim();
        var estado = $('select[name="idEstado"]').val();

        // Validar nombre
        if (nombre === '') {
            mostrarError('nombreMunicipio', 'El nombre del municipio es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreMunicipio', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nombreMunicipio', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nombreMunicipio', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        // Validar estado
        if (estado === '' || estado === null || estado === undefined) {
            mostrarError('idEstado', 'Debe seleccionar un estado.');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // VALIDAR FORMULARIO EDITAR
    // ═══════════════════════════════════════════════
    function validarFormEditar() {
        limpiarErrores('formEditarMunicipio');
        var esValido = true;
        var nombre = $('#nombreMunicipioEdit').val().trim();
        var estado = $('#idEstadoEdit').val();

        // Validar nombre
        if (nombre === '') {
            mostrarError('nombreMunicipioEdit', 'El nombre del municipio es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreMunicipioEdit', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nombreMunicipioEdit', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nombreMunicipioEdit', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        // Validar estado
        if (estado === '' || estado === null || estado === undefined) {
            mostrarError('idEstadoEdit', 'Debe seleccionar un estado.');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // LIMPIAR AL CERRAR MODALES
    // ═══════════════════════════════════════════════
    $('#modalMunicipio').on('hidden.bs.modal', function() {
        $('#formNuevoMunicipio')[0].reset();
        limpiarErrores('formNuevoMunicipio');
    });

    $('#modalEditarMunicipio').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarMunicipio');
    });

    // ═══════════════════════════════════════════════
    // GUARDAR NUEVO MUNICIPIO
    // ═══════════════════════════════════════════════
    $('#formNuevoMunicipio').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return false;
        }

        var $form = $(this);

        $.ajax({
            url: 'index.php?action=guardarMunicipio',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalMunicipio').modal('hide');
                    $form[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="4"]').length > 0) {
                        $('table tbody').empty();
                    }

                    var nuevaFila = '<tr style="display:none;">' +
                        '<td><span class="badge bg-secondary px-2 py-1">' + response.id + '</span></td>' +
                        '<td class="fw-bold text-dark"><span>' + response.nombre + '</span></td>' +
                        '<td class="text-muted small"><span>' + response.estado + '</span></td>' +
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

    // ═══════════════════════════════════════════════
    // CARGAR DATOS EN MODAL DE EDICIÓN
    // ═══════════════════════════════════════════════
    $(document).on('click', '.btn-editar', function() {
        var idMunicipio = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarMunicipio&id=' + idMunicipio,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idMunicipioEdit').val(data.idMunicipio);
                    $('#nombreMunicipioEdit').val(data.nombreMunicipio);
                    $('#idEstadoEdit').val(data.idEstado);
                    limpiarErrores('formEditarMunicipio');
                    $('#modalEditarMunicipio').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del municipio.", "danger");
            }
        });
    });

    // ═══════════════════════════════════════════════
    // ACTUALIZAR MUNICIPIO
    // ═══════════════════════════════════════════════
    $('#formEditarMunicipio').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return false;
        }

        var idActualizado = $('#idMunicipioEdit').val();
        var nombreNuevo = $('#nombreMunicipioEdit').val();
        var estadoNuevo = $('#idEstadoEdit option:selected').text();

        $.ajax({
            url: 'index.php?action=editarMunicipio',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarMunicipio').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    var fila = $('button[data-id="' + idActualizado + '"]').closest('tr');
                    fila.find('td:nth-child(2) span').text(nombreNuevo);
                    fila.find('td:nth-child(3) span').text(estadoNuevo);
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

    // ═══════════════════════════════════════════════
    // ELIMINAR MUNICIPIO
    // ═══════════════════════════════════════════════
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var idMunicipio = $(this).data('id');
        var fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este municipio?')) {
            $.ajax({
                url: 'index.php?action=eliminarMunicipio',
                type: 'POST',
                data: { idMunicipio: idMunicipio },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.fadeOut(600, function() {
                            $(this).remove();
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="4" class="text-center py-4 text-muted">No hay municipios registrados.</td></tr>');
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