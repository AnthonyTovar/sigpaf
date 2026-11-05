// Gestión de lugares de actividad

$(document).ready(function() {

    function lanzarAviso(mensaje, tipo) {
    Validar.aviso(mensaje, tipo);
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


    // Cascada Estado -> Municipio -> Parroquia

    function resetSelect($sel, placeholder, deshabilitar) {
        $sel.html(`<option value="" selected disabled>${placeholder}</option>`);
        $sel.prop('disabled', deshabilitar);
    }

    function cargarMunicipios($selMun, $selPar, idEstado, seleccionarMun, seleccionarPar) {
        resetSelect($selMun, 'Cargando municipios...', true);
        resetSelect($selPar, 'Seleccione primero un municipio...', true);

        $.ajax({
            url: 'index.php?action=municipiosPorEstado',
            type: 'GET',
            data: { idEstado: idEstado },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.municipios.length > 0) {
                    let opciones = '<option value="" selected disabled>Seleccione un municipio...</option>';
                    response.municipios.forEach(function(m) {
                        opciones += `<option value="${m.idMunicipio}">${m.nombreMunicipio}</option>`;
                    });
                    $selMun.html(opciones).prop('disabled', false);
                    if (seleccionarMun) {
                        $selMun.val(seleccionarMun);
                        cargarParroquias($selPar, seleccionarMun, seleccionarPar);
                    }
                } else {
                    resetSelect($selMun, 'Sin municipios registrados...', true);
                }
            },
            error: function() {
                resetSelect($selMun, 'Error al cargar municipios...', true);
                lanzarAviso('No se pudieron cargar los municipios.', 'danger');
            }
        });
    }

    function cargarParroquias($selPar, idMunicipio, seleccionarPar) {
        resetSelect($selPar, 'Cargando parroquias...', true);

        $.ajax({
            url: 'index.php?action=parroquiasPorMunicipio',
            type: 'GET',
            data: { idMunicipio: idMunicipio },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.parroquias.length > 0) {
                    let opciones = '<option value="" selected disabled>Seleccione una parroquia...</option>';
                    response.parroquias.forEach(function(p) {
                        opciones += `<option value="${p.idParroquia}">${p.nombreParroquia}</option>`;
                    });
                    $selPar.html(opciones).prop('disabled', false);
                    if (seleccionarPar) {
                        $selPar.val(seleccionarPar);
                    }
                } else {
                    resetSelect($selPar, 'Sin parroquias registradas...', true);
                }
            },
            error: function() {
                resetSelect($selPar, 'Error al cargar parroquias...', true);
                lanzarAviso('No se pudieron cargar las parroquias.', 'danger');
            }
        });
    }

    // Cascada del modal nuevo
    $('#idEstadoLugar').on('change', function() {
        cargarMunicipios($('#idMunicipioLugar'), $('#idParroquiaLugar'), $(this).val(), null, null);
    });
    $('#idMunicipioLugar').on('change', function() {
        cargarParroquias($('#idParroquiaLugar'), $(this).val(), null);
    });

    // Cascada del modal editar (sin preselección al cambiar manualmente)
    $('#idEstadoLugarEdit').on('change', function() {
        cargarMunicipios($('#idMunicipioLugarEdit'), $('#idParroquiaEdit'), $(this).val(), null, null);
    });
    $('#idMunicipioLugarEdit').on('change', function() {
        cargarParroquias($('#idParroquiaEdit'), $(this).val(), null);
    });

    // Texto "Parroquia, Municipio, Estado" para mostrar en la tabla
    function textoUbicacion($selPar, $selMun, $selEst) {
        return `${$selPar.find('option:selected').text()}, ${$selMun.find('option:selected').text()}, ${$selEst.find('option:selected').text()}`;
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
        resetSelect($('#idMunicipioLugar'), 'Seleccione primero un estado...', true);
        resetSelect($('#idParroquiaLugar'), 'Seleccione primero un municipio...', true);
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

        const nomParroquia = textoUbicacion($('#idParroquiaLugar'), $('#idMunicipioLugar'), $('#idEstadoLugar'));

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

                    // Preseleccionar la cascada geográfica del lugar
                    resetSelect($('#idMunicipioLugarEdit'), 'Seleccione primero un estado...', true);
                    resetSelect($('#idParroquiaEdit'), 'Seleccione primero un municipio...', true);
                    if (data.idEstado) {
                        $('#idEstadoLugarEdit').val(data.idEstado);
                        cargarMunicipios($('#idMunicipioLugarEdit'), $('#idParroquiaEdit'), data.idEstado, data.idMunicipio, data.idParroquia);
                    } else {
                        $('#idEstadoLugarEdit').val('');
                    }

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
        const nuevaParroquia = textoUbicacion($('#idParroquiaEdit'), $('#idMunicipioLugarEdit'), $('#idEstadoLugarEdit'));

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

        Validar.confirmar('Estas seguro de eliminar este lugar de actividad?', function() {
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
        });
    });
});