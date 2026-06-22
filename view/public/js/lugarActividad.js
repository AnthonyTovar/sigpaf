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
        limpiarErrores('formNuevoLugarActividad');

        const nombre = $('[name="nomLugarActividad"]').val().trim();
        const direccion = $('[name="direccion"]').val().trim();
        const descripcion = $('[name="desLugarActividad"]').val().trim();
        const idParroquia = $('[name="idParroquia"]').val();

        if (nombre === '') {
            mostrarError('nomLugarActividad', 'El nombre del lugar es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nomLugarActividad', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 100) {
            mostrarError('nomLugarActividad', 'El nombre no puede exceder los 100 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-]+$/.test(nombre)) {
            mostrarError('nomLugarActividad', 'El nombre solo puede contener letras, números, espacios y guiones.');
            esValido = false;
        }

        if (direccion === '') {
            mostrarError('direccion', 'La dirección es obligatoria.');
            esValido = false;
        } else if (direccion.length < 5) {
            mostrarError('direccion', 'La dirección debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (direccion.length > 255) {
            mostrarError('direccion', 'La dirección no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (descripcion !== '' && descripcion.length > 255) {
            mostrarError('desLugarActividad', 'La descripción no puede exceder los 255 caracteres.');
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
        limpiarErrores('formEditarLugarActividad');

        const nombre = $('#nomLugarActividadEdit').val().trim();
        const direccion = $('#direccionEdit').val().trim();
        const descripcion = $('#desLugarActividadEdit').val().trim();
        const idParroquia = $('#idParroquiaEdit').val();

        if (nombre === '') {
            mostrarError('nomLugarActividadEdit', 'El nombre del lugar es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nomLugarActividadEdit', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 100) {
            mostrarError('nomLugarActividadEdit', 'El nombre no puede exceder los 100 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-]+$/.test(nombre)) {
            mostrarError('nomLugarActividadEdit', 'El nombre solo puede contener letras, números, espacios y guiones.');
            esValido = false;
        }

        if (direccion === '') {
            mostrarError('direccionEdit', 'La dirección es obligatoria.');
            esValido = false;
        } else if (direccion.length < 5) {
            mostrarError('direccionEdit', 'La dirección debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (direccion.length > 255) {
            mostrarError('direccionEdit', 'La dirección no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (descripcion !== '' && descripcion.length > 255) {
            mostrarError('desLugarActividadEdit', 'La descripción no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (!idParroquia || idParroquia === '') {
            mostrarError('idParroquiaEdit', 'Debe seleccionar una parroquia.');
            esValido = false;
        }

        return esValido;
    }

    $('#modalLugarActividad').on('hidden.bs.modal', function() {
        $('#formNuevoLugarActividad')[0].reset();
        limpiarErrores('formNuevoLugarActividad');
    });

    $('#modalEditarLugarActividad').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarLugarActividad');
    });

    // GUARDAR
    $('#formNuevoLugarActividad').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const nombreParroquia = $('select[name="idParroquia"] option:selected').text();

        $.ajax({
            url: 'index.php?action=guardarLugarActividad',
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalLugarActividad').modal('hide');
                    $('#formNuevoLugarActividad')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="7"]').length > 0) {
                        $('table tbody').empty();
                    }

                    const esSedeBadge = response.esSede 
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Sede</span>'
                        : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> No</span>';

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td class="fw-bold text-dark"><span>${response.nombre}</span></td>
                            <td class="text-muted small"><span>${response.descripcion || 'N/A'}</span></td>
                            <td class="text-muted small"><span>${response.direccion}</span></td>
                            <td class="text-center">${esSedeBadge}</td>
                            <td class="text-muted small"><span>${nombreParroquia}</span></td>
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

    // CARGAR DATOS EN MODAL
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
                    $('#esSedeEdit').prop('checked', data.esSede == 1);
                    $('#idParroquiaEdit').val(data.idParroquia);
                    limpiarErrores('formEditarLugarActividad');
                    $('#modalEditarLugarActividad').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del lugar.", "danger");
            }
        });
    });

    // ACTUALIZAR
    $('#formEditarLugarActividad').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idLugarActividadEdit').val();
        const nuevoNombre = $('#nomLugarActividadEdit').val();
        const nuevaDesc = $('#desLugarActividadEdit').val() || 'N/A';
        const nuevaDireccion = $('#direccionEdit').val();
        const esSede = $('#esSedeEdit').is(':checked');
        const nombreParroquia = $('#idParroquiaEdit option:selected').text();

        $.ajax({
            url: 'index.php?action=editarLugarActividad', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarLugarActividad').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    
                    const esSedeBadge = esSede 
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Sede</span>'
                        : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> No</span>';
                    
                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNombre);
                    fila.find('td:nth-child(3) span').text(nuevaDesc);
                    fila.find('td:nth-child(4) span').text(nuevaDireccion);
                    fila.find('td:nth-child(5)').html(esSedeBadge);
                    fila.find('td:nth-child(6) span').text(nombreParroquia);
                    
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
        const idLugar = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este lugar de actividad?')) {
            $.ajax({
                url: 'index.php?action=eliminarLugarActividad',
                type: 'POST',
                data: { idLugarActividad: idLugar },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.css('transition', 'all 0.6s ease').addClass('fila-borrando').fadeOut(600, function() {
                            $(this).remove(); 
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay lugares de actividad registrados.</td></tr>');
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