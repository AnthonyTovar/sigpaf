$(document).ready(function() {

    // --- 1. FUNCIÓN GLOBAL (Avisos) ---
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

    // FUNCIÓN PARA VERIFICAR SI UN TEXTO YA EXISTE EN LA TABLA
    function existeEnTabla(texto, columnaIndex, idIgnorar = null) {
        let existe = false;
        const textoNormalizado = texto.trim().toLowerCase();

        if (!textoNormalizado || textoNormalizado === 'n/a') return false;

        $('table tbody tr').each(function() {
            const btnEditar = $(this).find('.btn-editar');
            const idFila = btnEditar.data('id');

            // Si estamos editando, ignoramos la fila del registro actual
            if (idIgnorar && String(idFila) === String(idIgnorar)) {
                return;
            }

            const valorCelda = $(this).find(`td:nth-child(${columnaIndex}) span`).text().trim().toLowerCase();
            if (valorCelda === textoNormalizado) {
                existe = true;
                return false; // Rompe el loop de jQuery
            }
        });

        return existe;
    }

    // BLOQUEO EN TIEMPO REAL: NO PERMITIR TIPEAMOS DE MÁS DE 2 CARACTERES IGUALES SEGUIDOS
    $(document).on('input', 'input[name="nomUnidadEjecutora"], textarea[name="desUnidadEjecutora"], #nomUnidadEjecutoraEdit, #desUnidadEjecutoraEdit', function() {
        let val = $(this).val();

        // Si se intentan ingresar 3 o más caracteres iguales seguidos, los recorta a máximo 2
        if (/(.)\1{2,}/g.test(val)) {
            val = val.replace(/(.)\1{2,}/g, '$1$1');
        }

        $(this).val(val);
    });

    // VALIDAR FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevaUnidadE');

        const nombre = $('[name="nomUnidadEjecutora"]').val().trim();
        const descripcion = $('[name="desUnidadEjecutora"]').val().trim();

        // Validar Nombre
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
        } else if (/(.)\1{2,}/.test(nombre)) {
            mostrarError('nomUnidadEjecutora', 'No se permiten 3 o más caracteres iguales seguidos.');
            esValido = false;
        } else if (existeEnTabla(nombre, 2)) {
            mostrarError('nomUnidadEjecutora', 'Este nombre de unidad ejecutora ya se encuentra registrado.');
            esValido = false;
        }

        // Validar Descripción
        if (descripcion !== '') {
            if (descripcion.length > 500) {
                mostrarError('desUnidadEjecutora', 'La descripción no puede exceder los 500 caracteres.');
                esValido = false;
            } else if (/(.)\1{2,}/.test(descripcion)) {
                mostrarError('desUnidadEjecutora', 'No se permiten 3 o más caracteres iguales seguidos.');
                esValido = false;
            } else if (existeEnTabla(descripcion, 3)) {
                mostrarError('desUnidadEjecutora', 'Esta descripción ya se encuentra registrada.');
                esValido = false;
            }
        }

        return esValido;
    }

    // VALIDAR FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarUnidadE');

        const id = $('#idUnidadEjecutoraEdit').val();
        const nombre = $('#nomUnidadEjecutoraEdit').val().trim();
        const descripcion = $('#desUnidadEjecutoraEdit').val().trim();

        // Validar Nombre
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
        } else if (/(.)\1{2,}/.test(nombre)) {
            mostrarError('nomUnidadEjecutoraEdit', 'No se permiten 3 o más caracteres iguales seguidos.');
            esValido = false;
        } else if (existeEnTabla(nombre, 2, id)) {
            mostrarError('nomUnidadEjecutoraEdit', 'Este nombre de unidad ejecutora ya se encuentra registrado.');
            esValido = false;
        }

        // Validar Descripción
        if (descripcion !== '') {
            if (descripcion.length > 500) {
                mostrarError('desUnidadEjecutoraEdit', 'La descripción no puede exceder los 500 caracteres.');
                esValido = false;
            } else if (/(.)\1{2,}/.test(descripcion)) {
                mostrarError('desUnidadEjecutoraEdit', 'No se permiten 3 o más caracteres iguales seguidos.');
                esValido = false;
            } else if (existeEnTabla(descripcion, 3, id)) {
                mostrarError('desUnidadEjecutoraEdit', 'Esta descripción ya se encuentra registrada.');
                esValido = false;
            }
        }

        return esValido;
    }

    // LIMPIAR ERRORES AL CERRAR MODALES
    $('#modalUnidadE').on('hidden.bs.modal', function() {
        $('#formNuevaUnidadE')[0].reset();
        limpiarErrores('formNuevaUnidadE');
    });

    $('#modalEditarUnidadE').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarUnidadE');
    });

    // --- 2. LÓGICA DE GUARDAR ---
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

                    // Verificar si hay mensaje vacío
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

    // --- 3. CARGAR DATOS EN MODAL ---
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

    // --- 4. LÓGICA DE ACTUALIZAR ---
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

    // --- 5. LÓGICA DE ELIMINAR ---
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idUnidadEjecutora = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de que deseas eliminar esta Unidad Ejecutora?')) {
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
        }
    });
});