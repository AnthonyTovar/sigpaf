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
            errorDiv.text(mensaje).css('display', 'block');
        }
    }

    function limpiarErrores(formId) {
        $('#' + formId + ' input').removeClass('is-invalid');
        $('#' + formId + ' select').removeClass('is-invalid');
        $('#' + formId + ' .invalid-feedback').text('').css('display', 'none');
    }

    // ═══════════════════════════════════════════════
    // VALIDAR FORMULARIO NUEVO
    // ═══════════════════════════════════════════════
    function validarFormNuevo() {
        limpiarErrores('formNuevaParroquia');
        var esValido = true;
        var nombre = $('input[name="nombreParroquia"]').val().trim();
        var municipio = $('select[name="idMunicipio"]').val();

        // Validar nombre
        if (nombre === '') {
            mostrarError('nombreParroquia', 'El nombre de la parroquia es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nombreParroquia', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 25) {
            mostrarError('nombreParroquia', 'El nombre no puede exceder los 25 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nombreParroquia', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        // Validar municipio
        if (municipio === '' || municipio === null || municipio === undefined) {
            mostrarError('idMunicipio', 'Debe seleccionar un municipio.');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // VALIDAR FORMULARIO EDITAR
    // ═══════════════════════════════════════════════
    function validarFormEditar() {
        limpiarErrores('formEditarParroquia');
        var esValido = true;
        var nombre = $('#nombreParroquiaEdit').val().trim();
        var municipio = $('#idMunicipioEdit').val();

        // Validar nombre
        if (nombre === '') {
            mostrarError('nombreParroquiaEdit', 'El nombre de la parroquia es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nombreParroquiaEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 25) {
            mostrarError('nombreParroquiaEdit', 'El nombre no puede exceder los 25 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nombreParroquiaEdit', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        // Validar municipio
        if (municipio === '' || municipio === null || municipio === undefined) {
            mostrarError('idMunicipioEdit', 'Debe seleccionar un municipio.');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // LIMPIAR AL CERRAR MODALES
    // ═══════════════════════════════════════════════
    $('#modalParroquia').on('hidden.bs.modal', function() {
        $('#formNuevaParroquia')[0].reset();
        limpiarErrores('formNuevaParroquia');
    });

    $('#modalEditarParroquia').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarParroquia');
    });

    // ═══════════════════════════════════════════════
    // GUARDAR NUEVA PARROQUIA
    // ═══════════════════════════════════════════════
    $('#formNuevaParroquia').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return false;
        }

        var $form = $(this);
        var idMunicipioSeleccionado = $('select[name="idMunicipio"] option:selected').text();

        $.ajax({
            url: 'index.php?action=guardarParroquia',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalParroquia').modal('hide');
                    $form[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="4"]').length > 0) {
                        $('table tbody').empty();
                    }

                    var nuevaFila = '<tr style="display:none;">' +
                        '<td><span class="badge bg-secondary px-2 py-1">' + response.id + '</span></td>' +
                        '<td class="fw-bold text-dark"><span>' + response.nombre + '</span></td>' +
                        '<td class="text-muted small"><span>' + idMunicipioSeleccionado + '</span></td>' +
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
        var idParroquia = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarParroquia&id=' + idParroquia,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idParroquiaEdit').val(data.idParroquia);
                    $('#nombreParroquiaEdit').val(data.nombreParroquia);
                    $('#idMunicipioEdit').val(data.idMunicipio);
                    limpiarErrores('formEditarParroquia');
                    $('#modalEditarParroquia').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos de la parroquia.", "danger");
            }
        });
    });

    // ═══════════════════════════════════════════════
    // ACTUALIZAR PARROQUIA
    // ═══════════════════════════════════════════════
    $('#formEditarParroquia').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return false;
        }

        var idActualizado = $('#idParroquiaEdit').val();
        var nombreNuevo = $('#nombreParroquiaEdit').val();
        var nuevoMunicipio = $('#idMunicipioEdit option:selected').text();

        $.ajax({
            url: 'index.php?action=editarParroquia',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarParroquia').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    var fila = $('button[data-id="' + idActualizado + '"]').closest('tr');
                    fila.find('td:nth-child(2) span').text(nombreNuevo);
                    fila.find('td:nth-child(3) span').text(nuevoMunicipio);
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
    // ELIMINAR PARROQUIA
    // ═══════════════════════════════════════════════
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var idParroquia = $(this).data('id');
        var fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar esta parroquia?')) {
            $.ajax({
                url: 'index.php?action=eliminarParroquia',
                type: 'POST',
                data: { idParroquia: idParroquia },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.fadeOut(600, function() {
                            $(this).remove();
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay parroquias registradas actualmente.</td></tr>');
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