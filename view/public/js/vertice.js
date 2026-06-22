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

    // FUNCIONES PARA MOSTRAR/OCULTAR ERRORES
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

    // VALIDAR FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoVertice');

        const nombre = $('[name="nombreVertice"]').val().trim();
        const descripcion = $('[name="descripcionVertice"]').val().trim();
        const idAreaE = $('[name="idAreaE"]').val();

        if (nombre === '') {
            mostrarError('nombreVertice', 'El nombre del vértice es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreVertice', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 150) {
            mostrarError('nombreVertice', 'El nombre no puede exceder los 150 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-]+$/.test(nombre)) {
            mostrarError('nombreVertice', 'El nombre solo puede contener letras, números, espacios y guiones.');
            esValido = false;
        }

        if (descripcion === '') {
            mostrarError('descripcionVertice', 'La descripción es obligatoria.');
            esValido = false;
        } else if (descripcion.length < 5) {
            mostrarError('descripcionVertice', 'La descripción debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (descripcion.length > 250) {
            mostrarError('descripcionVertice', 'La descripción no puede exceder los 250 caracteres.');
            esValido = false;
        }

        if (!idAreaE || idAreaE === '') {
            mostrarError('idAreaE', 'Debe seleccionar un área específica.');
            esValido = false;
        }

        return esValido;
    }

    // VALIDAR FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarVertice');

        const nombre = $('#nombreVerticeEdit').val().trim();
        const descripcion = $('#descripcionVerticeEdit').val().trim();
        const idAreaE = $('#idAreaEEdit').val();

        if (nombre === '') {
            mostrarError('nombreVerticeEdit', 'El nombre del vértice es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreVerticeEdit', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 150) {
            mostrarError('nombreVerticeEdit', 'El nombre no puede exceder los 150 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-]+$/.test(nombre)) {
            mostrarError('nombreVerticeEdit', 'El nombre solo puede contener letras, números, espacios y guiones.');
            esValido = false;
        }

        if (descripcion === '') {
            mostrarError('descripcionVerticeEdit', 'La descripción es obligatoria.');
            esValido = false;
        } else if (descripcion.length < 5) {
            mostrarError('descripcionVerticeEdit', 'La descripción debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (descripcion.length > 250) {
            mostrarError('descripcionVerticeEdit', 'La descripción no puede exceder los 250 caracteres.');
            esValido = false;
        }

        if (!idAreaE || idAreaE === '') {
            mostrarError('idAreaEEdit', 'Debe seleccionar un área específica.');
            esValido = false;
        }

        return esValido;
    }

    // LIMPIAR ERRORES AL CERRAR MODALES
    $('#modalVertice').on('hidden.bs.modal', function() {
        $('#formNuevoVertice')[0].reset();
        limpiarErrores('formNuevoVertice');
    });

    $('#modalEditarVertice').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarVertice');
    });

    // GUARDAR
    $('#formNuevoVertice').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const nombreArea = $('select[name="idAreaE"] option:selected').text();

        $.ajax({
            url: 'index.php?action=guardarVertice',
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalVertice').modal('hide');
                    $('#formNuevoVertice')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="5"]').length > 0) {
                        $('table tbody').empty();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td class="fw-bold text-dark"><span>${response.nombre}</span></td>
                            <td class="text-muted small"><span>${response.descripcion}</span></td>
                            <td class="text-muted small"><span>${nombreArea}</span></td>
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
        const idVertice = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarVertice&id=' + idVertice,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idVerticeEdit').val(data.idVertice);
                    $('#nombreVerticeEdit').val(data.nombreVertice);
                    $('#descripcionVerticeEdit').val(data.descVertice);
                    $('#idAreaEEdit').val(data.idAreaE);
                    limpiarErrores('formEditarVertice');
                    $('#modalEditarVertice').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del vértice.", "danger");
            }
        });
    });

    // ACTUALIZAR
    $('#formEditarVertice').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idVerticeEdit').val();
        const nombreNuevo = $('#nombreVerticeEdit').val();
        const descNueva = $('#descripcionVerticeEdit').val();
        const nombreArea = $('#idAreaEEdit option:selected').text();

        $.ajax({
            url: 'index.php?action=editarVertice', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarVertice').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    
                    const fila = $(`button[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nombreNuevo);
                    fila.find('td:nth-child(3) span').text(descNueva);
                    fila.find('td:nth-child(4) span').text(nombreArea);
                    
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
        const idVertice = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro?')) {
            $.ajax({
                url: 'index.php?action=eliminarVertice',
                type: 'POST',
                data: { idVertice: idVertice },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.css('transition', 'all 0.6s ease').addClass('fila-borrando').fadeOut(600, function() {
                            $(this).remove(); 
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay Vértices registrados actualmente.</td></tr>');
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