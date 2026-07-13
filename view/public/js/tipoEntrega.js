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
        $(`#${formId} input`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoTipoEntrega');

        const nomTipEntrega = $('[name="nomTipEntrega"]').val().trim();

        if (nomTipEntrega === '') {
            mostrarError('nomTipEntrega', 'El nombre del tipo de entrega es obligatorio.');
            esValido = false;
        } else if (nomTipEntrega.length < 2) {
            mostrarError('nomTipEntrega', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nomTipEntrega.length > 50) {
            mostrarError('nomTipEntrega', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nomTipEntrega)) {
            mostrarError('nomTipEntrega', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        return esValido;
    }

    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarTipoEntrega');

        const nomTipEntrega = $('#nomTipEntregaEdit').val().trim();

        if (nomTipEntrega === '') {
            mostrarError('nomTipEntregaEdit', 'El nombre del tipo de entrega es obligatorio.');
            esValido = false;
        } else if (nomTipEntrega.length < 2) {
            mostrarError('nomTipEntregaEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nomTipEntrega.length > 50) {
            mostrarError('nomTipEntregaEdit', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nomTipEntrega)) {
            mostrarError('nomTipEntregaEdit', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        }

        return esValido;
    }

    $('#modalTipoEntrega').on('hidden.bs.modal', function() {
        $('#formNuevoTipoEntrega')[0].reset();
        limpiarErrores('formNuevoTipoEntrega');
    });

    $('#modalEditarTipoEntrega').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarTipoEntrega');
    });

    $('#formNuevoTipoEntrega').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const formData = {
            nomTipEntrega: $('[name="nomTipEntrega"]').val().trim()
        };

        $.ajax({
            url: 'index.php?action=guardarTipoEntrega',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalTipoEntrega').modal('hide');
                    $('#formNuevoTipoEntrega')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="3"]').length > 0) {
                        $('table tbody tr td[colspan="3"]').closest('tr').remove();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.nomTipEntrega}</span></td>
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
        const idTipEntrega = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarTipoEntrega&id=' + idTipEntrega,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idTipEntregaEdit').val(data.idTipEntrega);
                    $('#nomTipEntregaEdit').val(data.nomTipEntrega);
                    limpiarErrores('formEditarTipoEntrega');
                    $('#modalEditarTipoEntrega').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos.", "danger");
            }
        });
    });

    $('#formEditarTipoEntrega').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idTipEntregaEdit').val();
        const nuevoNombre = $('#nomTipEntregaEdit').val();

        const formData = {
            idTipEntregaEdit: idActualizado,
            nomTipEntregaEdit: nuevoNombre
        };

        $.ajax({
            url: 'index.php?action=editarTipoEntrega',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarTipoEntrega').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNombre);
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
        const idTipEntrega = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('Estas seguro de eliminar este tipo de entrega?')) {
            $.ajax({
                url: 'index.php?action=eliminarTipoEntrega',
                type: 'POST',
                data: { idTipEntrega: idTipEntrega },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");

                        fila.fadeOut(600, function() {
                            $(this).remove();

                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="3" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay tipos de entrega registrados actualmente.</td></tr>');
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