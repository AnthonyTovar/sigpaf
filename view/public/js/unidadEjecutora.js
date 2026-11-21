// Gestión de Unidad Ejecutora

$(document).ready(function() {


    function lanzarAviso(mensaje, tipo) {
    Validar.aviso(mensaje, tipo);
}

    // Funciones para mostrar/ocultar errores
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

    // Función para verificar si un texto ya existe en la tabla
    function existeEnTabla(texto, columnaIndex, idIgnorar = null) {
        let existe = false;
        const textoNormalizado = texto.trim().toLowerCase();

        if (!textoNormalizado || textoNormalizado === 'n/a') return false;

        $('table tbody tr').each(function() {
            const btnEditar = $(this).find('.btn-editar');
            const idFila = btnEditar.data('id');


            if (idIgnorar && String(idFila) === String(idIgnorar)) {
                return;
            }

            const valorCelda = $(this).find(`td:nth-child(${columnaIndex}) span`).text().trim().toLowerCase();
            if (valorCelda === textoNormalizado) {
                existe = true;
                return false;
            }
        });

        return existe;
    }

    // Validar formulario nuevo
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevaUnidadE');

        const nombre = $('[name="nomUnidadEjecutora"]').val().trim();
        const descripcion = $('[name="desUnidadEjecutora"]').val().trim();


        if (nombre === '') {
            mostrarError('nomUnidadEjecutora', 'El nombre de la unidad es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nomUnidadEjecutora', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nomUnidadEjecutora', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-\+\&\/\.\,\(\)]+$/.test(nombre)) {
            mostrarError('nomUnidadEjecutora', 'El nombre contiene caracteres no permitidos.');
            esValido = false;
        } else if (/(.)\1{3,}/.test(nombre)) {
            mostrarError('nomUnidadEjecutora', 'No se permiten 4 o más caracteres iguales seguidos.');
            esValido = false;
        }


        if (descripcion !== '') {
            if (descripcion.length > 500) {
                mostrarError('desUnidadEjecutora', 'La descripción no puede exceder los 500 caracteres.');
                esValido = false;
            } else if (/(.)\1{3,}/.test(descripcion)) {
                mostrarError('desUnidadEjecutora', 'No se permiten 4 o más caracteres iguales seguidos.');
                esValido = false;
            }
        }

        return esValido;
    }

    // Validar formulario editar
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarUnidadE');

        const id = $('#idUnidadEjecutoraEdit').val();
        const nombre = $('#nomUnidadEjecutoraEdit').val().trim();
        const descripcion = $('#desUnidadEjecutoraEdit').val().trim();


        if (nombre === '') {
            mostrarError('nomUnidadEjecutoraEdit', 'El nombre de la unidad es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nomUnidadEjecutoraEdit', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nomUnidadEjecutoraEdit', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-\+\&\/\.\,\(\)]+$/.test(nombre)) {
            mostrarError('nomUnidadEjecutoraEdit', 'El nombre contiene caracteres no permitidos.');
            esValido = false;
        } else if (/(.)\1{3,}/.test(nombre)) {
            mostrarError('nomUnidadEjecutoraEdit', 'No se permiten 4 o más caracteres iguales seguidos.');
            esValido = false;
        }


        if (descripcion !== '') {
            if (descripcion.length > 500) {
                mostrarError('desUnidadEjecutoraEdit', 'La descripción no puede exceder los 500 caracteres.');
                esValido = false;
            } else if (/(.)\1{3,}/.test(descripcion)) {
                mostrarError('desUnidadEjecutoraEdit', 'No se permiten 4 o más caracteres iguales seguidos.');
                esValido = false;
            }
        }

        return esValido;
    }

    // Limpiar errores al cerrar modales
    $('#modalUnidadE').on('hidden.bs.modal', function() {
        $('#formNuevaUnidadE')[0].reset();
        limpiarErrores('formNuevaUnidadE');
    });

    $('#modalEditarUnidadE').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarUnidadE');
    });

    // 2. lógica de guardar
    $('#formNuevaUnidadE').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        $.ajax({
            url: 'index.php?action=guardarUnidadE',
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalUnidadE').modal('hide');
                    $('#formNuevaUnidadE')[0].reset();
                    lanzarAviso(response.message, 'success');


                    if ($('table tbody tr td[colspan="4"]').length > 0) {
                        $('table tbody tr td[colspan="4"]').closest('tr').remove();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.nombre}</span></td>
                            <td><span class="text-muted small">${response.descripcion || 'N/A'}</span></td>
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

    // 3. cargar datos en modal
    $(document).on('click', '.btn-editar', function() {
        const idUnidadEjecutora = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarUnidadE&id=' + idUnidadEjecutora,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idUnidadEjecutoraEdit').val(data.idUnidadEjecutora);
                    $('#nomUnidadEjecutoraEdit').val(data.nomUnidadEjecutora);
                    $('#desUnidadEjecutoraEdit').val(data.desUnidadEjecutora);
                    limpiarErrores('formEditarUnidadE');
                    $('#modalEditarUnidadE').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos de la Unidad Ejecutora.", "danger");
            }
        });
    });

    // 4. lógica de actualizar
    $('#formEditarUnidadE').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idUnidadEjecutoraEdit').val();
        const nuevoNombre = $('#nomUnidadEjecutoraEdit').val();
        const nuevaDesc = $('#desUnidadEjecutoraEdit').val() || 'N/A';

        $.ajax({
            url: 'index.php?action=editarUnidadE', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarUnidadE').modal('hide');
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

    // 5. lógica de eliminar
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idUnidadEjecutora = $(this).data('id');
        const fila = $(this).closest('tr');

        Validar.confirmar('¿Estás seguro de que deseas eliminar esta Unidad Ejecutora?', function() {
            $.ajax({
                url: 'index.php?action=eliminarUnidadE',
                type: 'POST',
                data: { idUnidadEjecutora: idUnidadEjecutora },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        
                        fila.fadeOut(600, function() {
                            $(this).remove();
                            
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay Unidad Ejecutora registrada actualmente.</td></tr>');
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
        });
    });
});