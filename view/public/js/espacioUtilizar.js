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

    // ============================================================
    // VALIDACIÓN EN TIEMPO REAL: MÁXIMO 3 CARACTERES IGUALES CONSECUTIVOS
    // ============================================================
    function tieneRepeticionExcesiva(texto) {
        // Detecta 4 o más letras/números iguales seguidos
        return /([a-zA-Z0-9áéíóúÁÉÍÓÚñÑ])\1{3,}/.test(texto);
    }

    function bloquearRepeticiones(inputSelector, errorId) {
        let valorAnterior = '';
        const $input = $(inputSelector);

        $input.on('focus', function() {
            valorAnterior = $(this).val();
        });

        $input.on('input', function(e) {
            const valorActual = $(this).val();

            if (tieneRepeticionExcesiva(valorActual)) {
                // Revertir al valor anterior (impide la escritura)
                $(this).val(valorAnterior);

                // Mostrar error visual
                $(this).addClass('is-invalid');
                $(`#${errorId}`).text('No puedes escribir más de 3 letras o números iguales seguidos.').show();

                // Pequeña vibración visual opcional
                $(this).closest('.input-group, .mb-3').addClass('shake');
                setTimeout(() => {
                    $(this).closest('.input-group, .mb-3').removeClass('shake');
                }, 300);
            } else {
                // Si es válido, limpiar error y actualizar valor anterior
                $(this).removeClass('is-invalid');
                $(`#${errorId}`).text('').hide();
                valorAnterior = valorActual;
            }
        });

        // También interceptar paste (pegar)
        $input.on('paste', function(e) {
            setTimeout(() => {
                const valorPegado = $(this).val();
                if (tieneRepeticionExcesiva(valorPegado)) {
                    $(this).val(valorAnterior);
                    $(this).addClass('is-invalid');
                    $(`#${errorId}`).text('El texto pegado contiene más de 3 caracteres iguales seguidos.').show();
                } else {
                    valorAnterior = $(this).val();
                }
            }, 0);
        });
    }

    // Aplicar bloqueo a los campos de texto (nombre y descripción)
    bloquearRepeticiones('[name="nombreEspacioUtilizar"]', 'error-nombreEspacioUtilizar');
    bloquearRepeticiones('[name="descEspacio"]', 'error-descEspacio');
    bloquearRepeticiones('#nombreEspacioUtilizarEdit', 'error-nombreEspacioUtilizarEdit');
    bloquearRepeticiones('#descEspacioEdit', 'error-descEspacioEdit');

    // ============================================================

    // VALIDACIONES DEL FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoEspacio');

        const nombre = $('[name="nombreEspacioUtilizar"]').val().trim();
        const capacidad = $('[name="capacidad"]').val().trim();
        const descripcion = $('[name="descEspacio"]').val().trim();

        if (nombre === '') {
            mostrarError('nombreEspacioUtilizar', 'El nombre del espacio es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nombreEspacioUtilizar', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 150) {
            mostrarError('nombreEspacioUtilizar', 'El nombre no puede exceder los 150 caracteres.');
            esValido = false;
        } else if (tieneRepeticionExcesiva(nombre)) {
            mostrarError('nombreEspacioUtilizar', 'No puedes escribir más de 3 letras o números iguales seguidos.');
            esValido = false;
        }

        if (capacidad === '') {
            mostrarError('capacidad', 'La capacidad es obligatoria.');
            esValido = false;
        } else if (!/^\d+$/.test(capacidad)) {
            mostrarError('capacidad', 'La capacidad debe ser un número entero.');
            esValido = false;
        } else if (parseInt(capacidad) < 1) {
            mostrarError('capacidad', 'La capacidad debe ser mayor a 0.');
            esValido = false;
        } else if (parseInt(capacidad) > 9999) {
            mostrarError('capacidad', 'La capacidad no puede exceder 9999.');
            esValido = false;
        }

        if (descripcion.length > 255) {
            mostrarError('descEspacio', 'La descripción no puede exceder los 255 caracteres.');
            esValido = false;
        } else if (tieneRepeticionExcesiva(descripcion)) {
            mostrarError('descEspacio', 'No puedes escribir más de 3 letras o números iguales seguidos.');
            esValido = false;
        }

        return esValido;
    }

    // VALIDACIONES DEL FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarEspacio');

        const nombre = $('#nombreEspacioUtilizarEdit').val().trim();
        const capacidad = $('#capacidadEdit').val().trim();
        const descripcion = $('#descEspacioEdit').val().trim();

        if (nombre === '') {
            mostrarError('nombreEspacioUtilizarEdit', 'El nombre del espacio es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nombreEspacioUtilizarEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 150) {
            mostrarError('nombreEspacioUtilizarEdit', 'El nombre no puede exceder los 150 caracteres.');
            esValido = false;
        } else if (tieneRepeticionExcesiva(nombre)) {
            mostrarError('nombreEspacioUtilizarEdit', 'No puedes escribir más de 3 letras o números iguales seguidos.');
            esValido = false;
        }

        if (capacidad === '') {
            mostrarError('capacidadEdit', 'La capacidad es obligatoria.');
            esValido = false;
        } else if (!/^\d+$/.test(capacidad)) {
            mostrarError('capacidadEdit', 'La capacidad debe ser un número entero.');
            esValido = false;
        } else if (parseInt(capacidad) < 1) {
            mostrarError('capacidadEdit', 'La capacidad debe ser mayor a 0.');
            esValido = false;
        } else if (parseInt(capacidad) > 9999) {
            mostrarError('capacidadEdit', 'La capacidad no puede exceder 9999.');
            esValido = false;
        }

        if (descripcion.length > 255) {
            mostrarError('descEspacioEdit', 'La descripción no puede exceder los 255 caracteres.');
            esValido = false;
        } else if (tieneRepeticionExcesiva(descripcion)) {
            mostrarError('descEspacioEdit', 'No puedes escribir más de 3 letras o números iguales seguidos.');
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

        $.ajax({
            url: 'index.php?action=guardarEspacioUtilizar',
            type: 'POST', 
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEspacio').modal('hide');
                    $('#formNuevoEspacio')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="5"]').length > 0) {
                        $('table tbody tr td[colspan="5"]').closest('tr').remove();
                    }

                    const desc = response.descEspacio ? response.descEspacio : 'N/A';
                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.nombreEspacio}</span></td>
                            <td><span class="text-muted small">${desc}</span></td>
                            <td><span class="badge bg-info text-dark"><i class="bi bi-people-fill me-1"></i>${response.capacidad} personas</span></td>
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
            url: 'index.php?action=consultarEspacioUtilizar&id=' + idEspacio,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idEspacioUtilizarEdit').val(data.idEspacioUtilizar);
                    $('#nombreEspacioUtilizarEdit').val(data.nombreEspacioUtilizar);
                    $('#descEspacioEdit').val(data.descEspacio);
                    $('#capacidadEdit').val(data.capacidad);
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

        $.ajax({
            url: 'index.php?action=editarEspacioUtilizar', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarEspacio').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const desc = nuevaDesc ? nuevaDesc : 'N/A';
                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNombre);
                    fila.find('td:nth-child(3) span').text(desc);
                    fila.find('td:nth-child(4) span').html(`<i class="bi bi-people-fill me-1"></i>${nuevaCapacidad} personas`);

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
                url: 'index.php?action=eliminarEspacioUtilizar',
                type: 'POST',
                data: { idEspacioUtilizar: idEspacio },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");

                        fila.fadeOut(600, function() {
                            $(this).remove();

                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay espacios registrados actualmente.</td></tr>');
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