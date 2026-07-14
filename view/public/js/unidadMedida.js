$(document).ready(function() {

    function lanzarAviso(mensaje, tipo) {
        const alerta = $('#registro-alerta');
        const icono = $('#alerta-icono');
        const texto = $('#alerta-texto');

        alerta.removeClass('alert-success alert-danger alert-warning').addClass('alert-' + tipo);

        let iconClass = (tipo === 'success') ? 'bi-check-circle-fill' : 
                         (tipo === 'warning') ? 'bi-pencil-square' : 'bi-exclamation-triangle-fill';

        icono.attr('class', 'bi ' + iconClass + ' me-2');
        texto.text(mensaje);
        alerta.fadeIn(400).delay(3500).fadeOut(400);
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
        $(`#${formId} input, #${formId} select, #${formId} textarea`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoUnidadMedida');

        const nombre = $('[name="nomUnidadMedida"]').val().trim();
        const descripcion = $('[name="descUnidadMedida"]').val().trim();

        if (nombre === '') {
            mostrarError('nomUnidadMedida', 'El nombre de la unidad es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomUnidadMedida', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nomUnidadMedida', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        }

        if (descripcion !== '' && descripcion.length > 250) {
            mostrarError('descUnidadMedida', 'La descripción no puede exceder los 250 caracteres.');
            esValido = false;
        }

        return esValido;
    }

    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarUnidadMedida');

        const nombre = $('#nomUnidadMedidaEdit').val().trim();
        const descripcion = $('#descUnidadMedidaEdit').val().trim();

        if (nombre === '') {
            mostrarError('nomUnidadMedidaEdit', 'El nombre de la unidad es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomUnidadMedidaEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nomUnidadMedidaEdit', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        }

        if (descripcion !== '' && descripcion.length > 250) {
            mostrarError('descUnidadMedidaEdit', 'La descripción no puede exceder los 250 caracteres.');
            esValido = false;
        }

        return esValido;
    }

    $('#modalUnidadMedida').on('hidden.bs.modal', function() {
        $('#formNuevoUnidadMedida')[0].reset();
        limpiarErrores('formNuevoUnidadMedida');
    });

    $('#modalEditarUnidadMedida').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarUnidadMedida');
    });

    // GUARDAR
    $('#formNuevoUnidadMedida').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        $.ajax({
            url: 'index.php?action=guardarUnidadMedida',
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalUnidadMedida').modal('hide');
                    $('#formNuevoUnidadMedida')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="4"]').length > 0) {
                        $('table tbody').empty();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td class="fw-bold text-dark"><span>${response.nombre}</span></td>
                            <td class="text-muted small"><span>${response.descripcion || 'Sin descripción'}</span></td>
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
                lanzarAviso("Error al procesar el registro", "danger");
            }
        });
    });

    // CARGAR DATOS EN MODAL EDITAR
    $(document).on('click', '.btn-editar', function() {
        const idUnidad = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarUnidadMedida&id=' + idUnidad,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idUnidadMedidaEdit').val(data.idUnidadMedida);
                    $('#nomUnidadMedidaEdit').val(data.nomUnidadMedida);
                    $('#descUnidadMedidaEdit').val(data.descUnidadMedida);
                    limpiarErrores('formEditarUnidadMedida');
                    $('#modalEditarUnidadMedida').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos de la unidad.", "danger");
            }
        });
    });

    // ACTUALIZAR
    $('#formEditarUnidadMedida').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idUnidadMedidaEdit').val();
        const nuevoNombre = $('#nomUnidadMedidaEdit').val();
        const nuevaDesc = $('#descUnidadMedidaEdit').val() || 'Sin descripción';

        $.ajax({
            url: 'index.php?action=editarUnidadMedida', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarUnidadMedida').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
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

    // ELIMINAR
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idUnidad = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar esta unidad de medida?')) {
            $.ajax({
                url: 'index.php?action=eliminarUnidadMedida',
                type: 'POST',
                data: { idUnidadMedida: idUnidad },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.css('transition', 'all 0.6s ease').addClass('fila-borrando').fadeOut(600, function() {
                            $(this).remove(); 
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay unidades de medida registradas.</td></tr>');
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