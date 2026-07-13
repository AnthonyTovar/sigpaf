$(document).ready(function() {

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

    function mostrarError(campo, mensaje) {
        const input = $(`[name="${campo}"]`);
        const errorDiv = $(`#error-${campo}`);

        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).show();
        }
    }

    function limpiarErrores(formId) {
        $(`#${formId} input, #${formId} textarea`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevaEstrategia');

        const nomEstDesarrollo = $('[name="nomEstDesarrollo"]').val().trim();
        const descEstDesarrollo = $('[name="descEstDesarrollo"]').val().trim();

        if (nomEstDesarrollo === '') {
            mostrarError('nomEstDesarrollo', 'El nombre de la estrategia es obligatorio.');
            esValido = false;
        } else if (nomEstDesarrollo.length < 2) {
            mostrarError('nomEstDesarrollo', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nomEstDesarrollo.length > 50) {
            mostrarError('nomEstDesarrollo', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\(\)]+$/.test(nomEstDesarrollo)) {
            mostrarError('nomEstDesarrollo', 'El nombre solo puede contener letras, números, espacios y paréntesis.');
            esValido = false;
        }

        if (descEstDesarrollo.length > 500) {
            mostrarError('descEstDesarrollo', 'La descripcion no puede exceder los 500 caracteres.');
            esValido = false;
        }

        return esValido;
    }

    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarEstrategia');

        const nomEstDesarrollo = $('#nomEstDesarrolloEdit').val().trim();
        const descEstDesarrollo = $('#descEstDesarrolloEdit').val().trim();

        if (nomEstDesarrollo === '') {
            mostrarError('nomEstDesarrolloEdit', 'El nombre de la estrategia es obligatorio.');
            esValido = false;
        } else if (nomEstDesarrollo.length < 2) {
            mostrarError('nomEstDesarrolloEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nomEstDesarrollo.length > 50) {
            mostrarError('nomEstDesarrolloEdit', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\(\)]+$/.test(nomEstDesarrollo)) {
            mostrarError('nomEstDesarrolloEdit', 'El nombre solo puede contener letras, números, espacios y paréntesis.');
            esValido = false;
        }

        if (descEstDesarrollo.length > 500) {
            mostrarError('descEstDesarrolloEdit', 'La descripcion no puede exceder los 500 caracteres.');
            esValido = false;
        }

        return esValido;
    }

    $('#modalEstrategia').on('hidden.bs.modal', function() {
        $('#formNuevaEstrategia')[0].reset();
        limpiarErrores('formNuevaEstrategia');
    });

    $('#modalEditarEstrategia').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarEstrategia');
    });

    $('#formNuevaEstrategia').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const formData = {
            nomEstDesarrollo: $('[name="nomEstDesarrollo"]').val().trim(),
            descEstDesarrollo: $('[name="descEstDesarrollo"]').val().trim()
        };

        $.ajax({
            url: 'index.php?action=guardarEstrategiaDesarrollo',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEstrategia').modal('hide');
                    $('#formNuevaEstrategia')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="4"]').length > 0) {
                        $('table tbody tr td[colspan="4"]').closest('tr').remove();
                    }

                    const desc = response.descEstDesarrollo ? response.descEstDesarrollo : 'N/A';

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.nomEstDesarrollo}</span></td>
                            <td><span class="text-muted small">${desc}</span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="${response.id}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="${response.id}">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;

                    const $fila = $(nuevaFila);
                    $('table tbody').append($fila);
                    $fila.fadeIn(800);

                } else {
                    lanzarAviso(response.message, 'danger');
                }
            },
            error: function() {
                lanzarAviso("Error al procesar el registro.", "danger");
            }
        });
    });

    $(document).on('click', '.btn-editar', function() {
        const idEstDesarrollo = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarEstrategiaDesarrollo&id=' + idEstDesarrollo,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idEstDesarrolloEdit').val(data.idEstDesarrollo);
                    $('#nomEstDesarrolloEdit').val(data.nomEstDesarrollo);
                    $('#descEstDesarrolloEdit').val(data.descEstDesarrollo);
                    limpiarErrores('formEditarEstrategia');
                    $('#modalEditarEstrategia').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos.", "danger");
            }
        });
    });

    $('#formEditarEstrategia').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idEstDesarrolloEdit').val();
        const nuevoNombre = $('#nomEstDesarrolloEdit').val();
        const nuevaDesc = $('#descEstDesarrolloEdit').val();

        const formData = {
            idEstDesarrolloEdit: idActualizado,
            nomEstDesarrolloEdit: nuevoNombre,
            descEstDesarrolloEdit: nuevaDesc
        };

        $.ajax({
            url: 'index.php?action=editarEstrategiaDesarrollo',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarEstrategia').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const desc = nuevaDesc ? nuevaDesc : 'N/A';

                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNombre);
                    fila.find('td:nth-child(3) span').text(desc);
                    fila.fadeOut(100).fadeIn(800);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            },
            error: function() {
                lanzarAviso("Error al actualizar el registro.", "danger");
            }
        });
    });

    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        const idEstDesarrollo = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('Estas seguro de eliminar esta estrategia de desarrollo?')) {
            $.ajax({
                url: 'index.php?action=eliminarEstrategiaDesarrollo',
                type: 'POST',
                data: { idEstDesarrollo: idEstDesarrollo },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");

                        fila.fadeOut(600, function() {
                            $(this).remove();

                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay estrategias de desarrollo registradas actualmente.</td></tr>');
                            }
                        });
                    } else {
                        lanzarAviso(response.message, "danger");
                    }
                },
                error: function() {
                    lanzarAviso("Ocurrio un error al intentar eliminar.", "danger");
                }
            });
        }
    });
});