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

    function mostrarError(campo, mensaje) {
        const input = $(`[name="${campo}"]`);
        const errorDiv = $(`#error-${campo}`);
        
        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).show();
        }
    }

    function limpiarErrores(formId) {
        $(`#${formId} input, #${formId} textarea`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    // BLOQUEO EN TIEMPO REAL: PERMITE HASTA 3 CARACTERES IGUALES Y BLOQUEA A PARTIR DEL 4TO
    $(document).on('input', 'input[name="nombreCargo"], textarea[name="descripcionCargo"], #nombreCargoEdit, #descripcionCargoEdit', function() {
        let val = $(this).val();

        // Limitar la descripción a un máximo de 100 caracteres
        if ($(this).attr('name') === 'descripcionCargo' || $(this).attr('id') === 'descripcionCargoEdit') {
            if (val.length > 100) {
                val = val.substring(0, 100);
            }
        }

        // Si se intentan ingresar 4 o más caracteres iguales seguidos, se recorta manteniendo máximo 3
        if (/(.)\1{3,}/g.test(val)) {
            val = val.replace(/(.)\1{3,}/g, '$1$1$1');
        }

        $(this).val(val);
    });

    // VALIDACIONES DEL FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoCargo');

        const nombre = $('[name="nombreCargo"]').val().trim();
        const descripcion = $('[name="descripcionCargo"]').val().trim();

        if (nombre === '') {
            mostrarError('nombreCargo', 'El nombre del cargo es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreCargo', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 15) {
            mostrarError('nombreCargo', 'El nombre no puede exceder los 15 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nombreCargo', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        } else if (/(.)\1{3,}/.test(nombre)) {
            mostrarError('nombreCargo', 'No se permiten más de 3 caracteres iguales seguidos.');
            esValido = false;
        }

        // Validar descripción (máximo 100 caracteres)
        if (descripcion.length > 100) {
            mostrarError('descripcionCargo', 'La descripción no puede exceder los 100 caracteres.');
            esValido = false;
        } else if (/(.)\1{3,}/.test(descripcion)) {
            mostrarError('descripcionCargo', 'No se permiten más de 3 caracteres iguales seguidos.');
            esValido = false;
        }

        return esValido;
    }

    // VALIDACIONES DEL FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarCargo');

        const nombre = $('#nombreCargoEdit').val().trim();
        const descripcion = $('#descripcionCargoEdit').val().trim();

        if (nombre === '') {
            mostrarError('nombreCargoEdit', 'El nombre del cargo es obligatorio.');
            esValido = false;
        } else if (nombre.length < 3) {
            mostrarError('nombreCargoEdit', 'El nombre debe tener al menos 3 caracteres.');
            esValido = false;
        } else if (nombre.length > 15) {
            mostrarError('nombreCargoEdit', 'El nombre no puede exceder los 15 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            mostrarError('nombreCargoEdit', 'El nombre solo puede contener letras y espacios.');
            esValido = false;
        } else if (/(.)\1{3,}/.test(nombre)) {
            mostrarError('nombreCargoEdit', 'No se permiten más de 3 caracteres iguales seguidos.');
            esValido = false;
        }

        // Validar descripción (máximo 100 caracteres)
        if (descripcion.length > 100) {
            mostrarError('descripcionCargoEdit', 'La descripción no puede exceder los 100 caracteres.');
            esValido = false;
        } else if (/(.)\1{3,}/.test(descripcion)) {
            mostrarError('descripcionCargoEdit', 'No se permiten más de 3 caracteres iguales seguidos.');
            esValido = false;
        }

        return esValido;
    }

    // LIMPIAR ERRORES AL CERRAR MODALES
    $('#modalCargo').on('hidden.bs.modal', function() {
        $('#formNuevoCargo')[0].reset();
        limpiarErrores('formNuevoCargo');
    });

    $('#modalEditarCargo').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarCargo');
    });

    // LOGICA DE GUARDAR NUEVO
    $('#formNuevoCargo').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        $.ajax({
            url: 'index.php?action=guardarCargo',
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalCargo').modal('hide');
                    $('#formNuevoCargo')[0].reset();
                    lanzarAviso(response.message, 'success');

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.nombre}</span></td>
                            <td><span class="text-muted small">${response.descripcion}</span></td>
                            <td class="text-center">
                                <button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="${response.id}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="${response.id}">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
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

    // LOGICA DE EDITAR 
    $(document).on('click', '.btn-editar', function() {
        const idCargo = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarCargo&id=' + idCargo,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idCargoEdit').val(data.idCargo);
                    $('#nombreCargoEdit').val(data.nombreCargo);
                    $('#descripcionCargoEdit').val(data.descripcionCargo);
                    limpiarErrores('formEditarCargo');
                    $('#modalEditarCargo').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del cargo.", "danger");
            }
        });
    });

    // LOGICA DE ACTUALIZAR 
    $('#formEditarCargo').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idCargoEdit').val();
        const nuevoNombre = $('#nombreCargoEdit').val();
        const nuevaDesc = $('#descripcionCargoEdit').val();

        $.ajax({
            url: 'index.php?action=editarCargo', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarCargo').modal('hide');
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

    // LOGICA DE ELIMINAR
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idCargo = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de que deseas eliminar este cargo?')) {
            $.ajax({
                url: 'index.php?action=eliminarCargo',
                type: 'POST',
                data: { idCargo: idCargo },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        
                        fila.find('td').css({
                            'padding-top': '0',
                            'padding-bottom': '0',
                            'transition': 'all 0.6s ease'
                        });

                        fila.fadeOut(600, function() {
                            $(this).remove(); 
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