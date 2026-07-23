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

    // VALIDAR QUE NO HAYA MÁS DE 3 LETRAS REPETIDAS CONSECUTIVAS
    function tieneLetrasRepetidas(texto) {
        // Ignorar espacios para la validación de repetición
        const textoSinEspacios = texto.replace(/\s/g, '');
        return /(.)\1{3,}/i.test(textoSinEspacios);
    }

    // FUNCION PARA VIBRAR EL INPUT
    function vibrarInput(elemento) {
        $(elemento).addClass('vibrar-input');
        setTimeout(function() {
            $(elemento).removeClass('vibrar-input');
        }, 300);
    }

    // BLOQUEAR ESCRITURA SI HAY 3 LETRAS REPETIDAS (KEYDOWN)
    function bloquearRepetidas(e, input) {
        const valorActual = $(input).val();
        const tecla = e.key;
        
        // Ignorar teclas de control (backspace, flechas, etc.)
        if (e.ctrlKey || e.altKey || e.metaKey || tecla.length > 1) {
            return true;
        }
        
        const nuevoValor = valorActual + tecla;
        
        if (tieneLetrasRepetidas(nuevoValor)) {
            e.preventDefault();
            vibrarInput(input);
            mostrarError($(input).attr('name'), 'No puede haber letras repetidas más de 3 veces consecutivas.');
            return false;
        }
        
        // Limpiar error si ya no hay repetidas
        if (!tieneLetrasRepetidas(valorActual)) {
            $(input).removeClass('is-invalid');
            $(`#error-${$(input).attr('name')}`).text('').hide();
        }
        
        return true;
    }

    // BLOQUEAR ESCRITURA SI HAY 3 LETRAS REPETIDAS (INPUT/PASTE)
    function validarInputEnTiempoReal(input) {
        const valor = $(input).val();
        
        if (tieneLetrasRepetidas(valor)) {
            // Quitar el último carácter que causó la repetición
            $(input).val(valor.slice(0, -1));
            vibrarInput(input);
            mostrarError($(input).attr('name'), 'No puede haber letras repetidas más de 3 veces consecutivas.');
        } else {
            $(input).removeClass('is-invalid');
            $(`#error-${$(input).attr('name')}`).text('').hide();
        }
    }

    // VALIDAR FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoVertice');

        const nombre = $('[name="nombreVertice"]').val().trim();
        const descripcion = $('[name="descripcionVertice"]').val().trim();

        if (nombre === '') {
            mostrarError('nombreVertice', 'El nombre del vértice es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreVertice', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nombreVertice', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-]+$/.test(nombre)) {
            mostrarError('nombreVertice', 'El nombre solo puede contener letras, números, espacios y guiones.');
            esValido = false;
        } else if (tieneLetrasRepetidas(nombre)) {
            mostrarError('nombreVertice', 'No puede haber letras repetidas más de 3 veces consecutivas.');
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

        return esValido;
    }

    // VALIDAR FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarVertice');

        const nombre = $('#nombreVerticeEdit').val().trim();
        const descripcion = $('#descripcionVerticeEdit').val().trim();

        if (nombre === '') {
            mostrarError('nombreVerticeEdit', 'El nombre del vértice es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreVerticeEdit', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nombreVerticeEdit', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\d\-]+$/.test(nombre)) {
            mostrarError('nombreVerticeEdit', 'El nombre solo puede contener letras, números, espacios y guiones.');
            esValido = false;
        } else if (tieneLetrasRepetidas(nombre)) {
            mostrarError('nombreVerticeEdit', 'No puede haber letras repetidas más de 3 veces consecutivas.');
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

        return esValido;
    }

    // EVENTOS KEYDOWN PARA BLOQUEAR EN TIEMPO REAL (NUEVO)
    $('[name="nombreVertice"]').on('keydown', function(e) {
        return bloquearRepetidas(e, this);
    });

    // EVENTOS KEYDOWN PARA BLOQUEAR EN TIEMPO REAL (EDITAR)
    $('#nombreVerticeEdit').on('keydown', function(e) {
        return bloquearRepetidas(e, this);
    });

    // EVENTOS INPUT PARA CAPTURAR PEGADO Y OTRAS ENTRADAS (NUEVO)
    $('[name="nombreVertice"]').on('input', function() {
        validarInputEnTiempoReal(this);
    });

    // EVENTOS INPUT PARA CAPTURAR PEGADO Y OTRAS ENTRADAS (EDITAR)
    $('#nombreVerticeEdit').on('input', function() {
        validarInputEnTiempoReal(this);
    });

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

                    if ($('table tbody tr td[colspan="4"]').length > 0) {
                        $('table tbody').empty();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td class="fw-bold text-dark"><span>${response.nombre}</span></td>
                            <td class="text-muted small"><span>${response.descripcion}</span></td>
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
                                $('table tbody').append('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay Vértices registrados actualmente.</td></tr>');
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