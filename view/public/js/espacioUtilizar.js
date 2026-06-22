$(document).ready(function() {

    // FUNCIÓN GLOBAL
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

    // VALIDACIONES FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoEspacio');

        const nombre = $('[name="nombreEspacioUtilizar"]').val().trim();
        const descripcion = $('[name="descEspacio"]').val().trim();
        const capacidad = $('[name="capacidad"]').val().trim();
        const idLugarActividad = $('[name="idLugarActividad"]').val();

        if (nombre === '') {
            mostrarError('nombreEspacioUtilizar', 'El nombre del espacio es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreEspacioUtilizar', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 150) {
            mostrarError('nombreEspacioUtilizar', 'El nombre no puede exceder los 150 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-]+$/.test(nombre)) {
            mostrarError('nombreEspacioUtilizar', 'El nombre solo puede contener letras, números, espacios y guiones.');
            esValido = false;
        }

        if (descripcion === '') {
            mostrarError('descEspacio', 'La descripción es obligatoria.');
            esValido = false;
        } else if (descripcion.length < 5) {
            mostrarError('descEspacio', 'La descripción debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (descripcion.length > 255) {
            mostrarError('descEspacio', 'La descripción no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (capacidad === '') {
            mostrarError('capacidad', 'La capacidad es obligatoria.');
            esValido = false;
        } else if (!/^\d+$/.test(capacidad)) {
            mostrarError('capacidad', 'La capacidad debe ser un número entero.');
            esValido = false;
        } else {
            const capNum = parseInt(capacidad, 10);
            if (capNum < 1) {
                mostrarError('capacidad', 'La capacidad mínima es 1 persona.');
                esValido = false;
            } else if (capNum > 9999) {
                mostrarError('capacidad', 'La capacidad máxima es 9999 personas.');
                esValido = false;
            }
        }

        if (!idLugarActividad || idLugarActividad === '') {
            mostrarError('idLugarActividad', 'Debe seleccionar un lugar de actividad.');
            esValido = false;
        }

        return esValido;
    }

    // VALIDACIONES FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarEspacio');

        const nombre = $('#nombreEspacioUtilizarEdit').val().trim();
        const descripcion = $('#descEspacioEdit').val().trim();
        const capacidad = $('#capacidadEdit').val().trim();
        const idLugarActividad = $('#idLugarActividadEdit').val();

        if (nombre === '') {
            mostrarError('nombreEspacioUtilizarEdit', 'El nombre del espacio es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreEspacioUtilizarEdit', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 150) {
            mostrarError('nombreEspacioUtilizarEdit', 'El nombre no puede exceder los 150 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-]+$/.test(nombre)) {
            mostrarError('nombreEspacioUtilizarEdit', 'El nombre solo puede contener letras, números, espacios y guiones.');
            esValido = false;
        }

        if (descripcion === '') {
            mostrarError('descEspacioEdit', 'La descripción es obligatoria.');
            esValido = false;
        } else if (descripcion.length < 5) {
            mostrarError('descEspacioEdit', 'La descripción debe tener al menos 5 caracteres.');
            esValido = false;
        } else if (descripcion.length > 255) {
            mostrarError('descEspacioEdit', 'La descripción no puede exceder los 255 caracteres.');
            esValido = false;
        }

        if (capacidad === '') {
            mostrarError('capacidadEdit', 'La capacidad es obligatoria.');
            esValido = false;
        } else if (!/^\d+$/.test(capacidad)) {
            mostrarError('capacidadEdit', 'La capacidad debe ser un número entero.');
            esValido = false;
        } else {
            const capNum = parseInt(capacidad, 10);
            if (capNum < 1) {
                mostrarError('capacidadEdit', 'La capacidad mínima es 1 persona.');
                esValido = false;
            } else if (capNum > 9999) {
                mostrarError('capacidadEdit', 'La capacidad máxima es 9999 personas.');
                esValido = false;
            }
        }

        if (!idLugarActividad || idLugarActividad === '') {
            mostrarError('idLugarActividadEdit', 'Debe seleccionar un lugar de actividad.');
            esValido = false;
        }

        return esValido;
    }

    // LIMPIAR ERRORES AL CERRAR MODALES
    $('#modalEspacio').on('hidden.bs.modal', function() {
        $('#formNuevoEspacio')[0].reset();
        limpiarErrores('formNuevoEspacio');
    });

    $('#modalEditarEspacio').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarEspacio');
    });

    // GUARDAR NUEVO ESPACIO
    $('#formNuevoEspacio').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const nombreLugar = $('select[name="idLugarActividad"] option:selected').text();

        $.ajax({
            url: 'index.php?action=guardarEspacio',
            type: 'POST', 
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEspacio').modal('hide');
                    $('#formNuevoEspacio')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="6"]').length > 0) {
                        $('table tbody tr td[colspan="6"]').closest('tr').remove();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.nombre}</span></td>
                            <td><span class="text-muted small">${response.descripcion}</span></td>
                            <td><span class="badge bg-info text-dark">${response.capacidad} personas</span></td>
                            <td><span class="text-muted small">${nombreLugar}</span></td>
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

    // CARGAR DATOS EN MODAL DE EDICIÓN
    $(document).on('click', '.btn-editar', function() {
        const idEspacio = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarEspacio&id=' + idEspacio,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idEspacioUtilizarEdit').val(data.idEspacioUtilizar);
                    $('#nombreEspacioUtilizarEdit').val(data.nombreEspacioUtilizar);
                    $('#descEspacioEdit').val(data.descEspacio);
                    $('#capacidadEdit').val(data.capacidad);
                    $('#idLugarActividadEdit').val(data.idLugarActividad);
                    limpiarErrores('formEditarEspacio');
                    $('#modalEditarEspacio').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del espacio.", "danger");
            }
        });
    });

    // ACTUALIZAR ESPACIO
    $('#formEditarEspacio').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idEspacioUtilizarEdit').val();
        const nuevoNombre = $('#nombreEspacioUtilizarEdit').val();
        const nuevaDesc = $('#descEspacioEdit').val();
        const nuevaCapacidad = $('#capacidadEdit').val();
        const nuevoLugar = $('#idLugarActividadEdit option:selected').text();

        $.ajax({
            url: 'index.php?action=editarEspacio', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarEspacio').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    
                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNombre);
                    fila.find('td:nth-child(3) span').text(nuevaDesc);
                    fila.find('td:nth-child(4) span').text(nuevaCapacidad + ' personas');
                    fila.find('td:nth-child(5) span').text(nuevoLugar);
                    
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

    // ELIMINAR ESPACIO
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idEspacio = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este espacio?')) {
            $.ajax({
                url: 'index.php?action=eliminarEspacio',
                type: 'POST',
                data: { idEspacioUtilizar: idEspacio },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        
                        fila.fadeOut(600, function() {
                            $(this).remove();
                            
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay espacios registrados actualmente.</td></tr>');
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