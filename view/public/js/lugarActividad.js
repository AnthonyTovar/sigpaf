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
        $(`#${formId} input, #${formId} select, #${formId} textarea`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoLugar');

        const nomLugar = $('[name="nomLugarActividad"]').val().trim();
        const direccion = $('[name="direccion"]').val().trim();
        const desLugar = $('[name="desLugarActividad"]').val().trim();
        const idParroquia = $('[name="idParroquia"]').val();

        if (nomLugar === '') {
            mostrarError('nomLugarActividad', 'El nombre del lugar es obligatorio.');
            esValido = false;
        } else if (nomLugar.length < 2) {
            mostrarError('nomLugarActividad', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nomLugar.length > 100) {
            mostrarError('nomLugarActividad', 'El nombre no puede exceder los 100 caracteres.');
            esValido = false;
        }

        if (direccion === '') {
            mostrarError('direccion', 'La direccion es obligatoria.');
            esValido = false;
        } else if (direccion.length < 5) {
            mostrarError('direccion', 'La direccion debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (direccion.length > 255) {
            mostrarError('direccion', 'La direccion no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (desLugar.length > 255) {
            mostrarError('desLugarActividad', 'La descripcion no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (!idParroquia || idParroquia === '') {
            mostrarError('idParroquia', 'Debe seleccionar una parroquia.');
            esValido = false;
        }

        return esValido;
    }

    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarLugar');

        const nomLugar = $('#nomLugarActividadEdit').val().trim();
        const direccion = $('#direccionEdit').val().trim();
        const desLugar = $('#desLugarActividadEdit').val().trim();
        const idParroquia = $('#idParroquiaEdit').val();

        if (nomLugar === '') {
            mostrarError('nomLugarActividadEdit', 'El nombre del lugar es obligatorio.');
            esValido = false;
        } else if (nomLugar.length < 2) {
            mostrarError('nomLugarActividadEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nomLugar.length > 100) {
            mostrarError('nomLugarActividadEdit', 'El nombre no puede exceder los 100 caracteres.');
            esValido = false;
        }

        if (direccion === '') {
            mostrarError('direccionEdit', 'La direccion es obligatoria.');
            esValido = false;
        } else if (direccion.length < 5) {
            mostrarError('direccionEdit', 'La direccion debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (direccion.length > 255) {
            mostrarError('direccionEdit', 'La direccion no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (desLugar.length > 255) {
            mostrarError('desLugarActividadEdit', 'La descripcion no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (!idParroquia || idParroquia === '') {
            mostrarError('idParroquiaEdit', 'Debe seleccionar una parroquia.');
            esValido = false;
        }

        return esValido;
    }

    $('#modalLugar').on('hidden.bs.modal', function() {
        $('#formNuevoLugar')[0].reset();
        limpiarErrores('formNuevoLugar');
    });

    $('#modalEditarLugar').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarLugar');
    });

    $('#formNuevoLugar').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const nomParroquia = $('select[name="idParroquia"] option:selected').text();

        const formData = {
            nomLugarActividad: $('[name="nomLugarActividad"]').val().trim(),
            desLugarActividad: $('[name="desLugarActividad"]').val().trim(),
            direccion: $('[name="direccion"]').val().trim(),
            esSede: $('[name="esSede"]').is(':checked') ? '1' : '0',
            idParroquia: $('[name="idParroquia"]').val()
        };

        $.ajax({
            url: 'index.php?action=guardarLugarActividad',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalLugar').modal('hide');
                    $('#formNuevoLugar')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="7"]').length > 0) {
                        $('table tbody tr td[colspan="7"]').closest('tr').remove();
                    }

                    const des = response.desLugar ? response.desLugar : 'N/A';
                    const sedeBadge = response.esSede 
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Si</span>'
                        : '<span class="badge bg-secondary"><i class="bi bi-x-circle-fill me-1"></i>No</span>';

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.nomLugar}</span></td>
                            <td><span class="text-muted small">${des}</span></td>
                            <td><span class="text-muted small">${response.direccion}</span></td>
                            <td class="text-center">${sedeBadge}</td>
                            <td><span class="text-muted small">${nomParroquia}</span></td>
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
        const idLugar = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarLugarActividad&id=' + idLugar,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idLugarActividadEdit').val(data.idLugarActividad);
                    $('#nomLugarActividadEdit').val(data.nomLugarActividad);
                    $('#desLugarActividadEdit').val(data.desLugarActividad);
                    $('#direccionEdit').val(data.direccion);
                    $('#esSedeEdit').prop('checked', data.esSede == 1 || data.esSede === true);
                    $('#idParroquiaEdit').val(data.idParroquia);
                    limpiarErrores('formEditarLugar');
                    $('#modalEditarLugar').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del lugar.", "danger");
            }
        });
    });

    $('#formEditarLugar').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idLugarActividadEdit').val();
        const nuevoNom = $('#nomLugarActividadEdit').val();
        const nuevaDes = $('#desLugarActividadEdit').val();
        const nuevaDir = $('#direccionEdit').val();
        const nuevaSede = $('#esSedeEdit').is(':checked');
        const nuevaParroquia = $('#idParroquiaEdit option:selected').text();

        const formData = {
            idLugarActividadEdit: idActualizado,
            nomLugarActividadEdit: nuevoNom,
            desLugarActividadEdit: nuevaDes,
            direccionEdit: nuevaDir,
            esSedeEdit: nuevaSede ? '1' : '0',
            idParroquiaEdit: $('#idParroquiaEdit').val()
        };

        $.ajax({
            url: 'index.php?action=editarLugarActividad',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarLugar').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const des = nuevaDes ? nuevaDes : 'N/A';
                    const sedeBadge = nuevaSede 
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Si</span>'
                        : '<span class="badge bg-secondary"><i class="bi bi-x-circle-fill me-1"></i>No</span>';

                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNom);
                    fila.find('td:nth-child(3) span').text(des);
                    fila.find('td:nth-child(4) span').text(nuevaDir);
                    fila.find('td:nth-child(5)').html(sedeBadge);
                    fila.find('td:nth-child(6) span').text(nuevaParroquia);

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
        const idLugar = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('Estas seguro de eliminar este lugar de actividad?')) {
            $.ajax({
                url: 'index.php?action=eliminarLugarActividad',
                type: 'POST',
                data: { idLugarActividad: idLugar },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");

                        fila.fadeOut(600, function() {
                            $(this).remove();

                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay lugares de actividad registrados actualmente.</td></tr>');
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