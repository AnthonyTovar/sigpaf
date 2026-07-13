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

    // VALIDAR NOMBRE DE USUARIO
    function validarNombreUsuario(nombre) {
        return /^[a-zA-Z0-9_.-]{3,50}$/.test(nombre);
    }

    // VALIDAR CONTRASEÑA
    function validarContrasena(contrasena, requerido = true) {
        if (!requerido && contrasena === '') return true;
        return contrasena.length >= 6 && contrasena.length <= 255;
    }

    // ============================================
    // BÚSQUEDA DE EMPLEADO POR CÉDULA
    // ============================================
    function filtrarEmpleados(inputId, selectId, infoId, textoId) {
        $(inputId).on('input', function() {
            const busqueda = $(this).val().trim().toLowerCase();
            const select = $(selectId);
            let encontrado = false;

            select.find('option').each(function() {
                if ($(this).val() === '') return; // Skip placeholder

                const cedula = $(this).data('cedula').toLowerCase();
                const texto = $(this).text().toLowerCase();

                if (cedula.includes(busqueda) || texto.includes(busqueda)) {
                    $(this).show();
                    if (!encontrado && busqueda.length > 0) {
                        select.val($(this).val());
                        mostrarInfoEmpleado(selectId, infoId, textoId);
                        encontrado = true;
                    }
                } else {
                    $(this).hide();
                }
            });

            if (!encontrado && busqueda.length === 0) {
                select.val('');
                $(infoId).hide();
            }
        });
    }

    function mostrarInfoEmpleado(selectId, infoId, textoId) {
        const select = $(selectId);
        const selected = select.find('option:selected');

        if (selected.val() && selected.val() !== '') {
            const texto = selected.text();
            $(textoId).text(texto);
            $(infoId).fadeIn(200);
        } else {
            $(infoId).hide();
        }
    }

    // Inicializar búsqueda en modal NUEVO
    filtrarEmpleados('#buscarEmpleado', '#idEmpleado', '#empleadoInfo', '#empleadoSeleccionadoTexto');

    $('#idEmpleado').on('change', function() {
        mostrarInfoEmpleado('#idEmpleado', '#empleadoInfo', '#empleadoSeleccionadoTexto');
    });

    // Inicializar búsqueda en modal EDITAR
    filtrarEmpleados('#buscarEmpleadoEdit', '#idEmpleadoEdit', '#empleadoInfoEdit', '#empleadoSeleccionadoTextoEdit');

    $('#idEmpleadoEdit').on('change', function() {
        mostrarInfoEmpleado('#idEmpleadoEdit', '#empleadoInfoEdit', '#empleadoSeleccionadoTextoEdit');
    });

    // VALIDACIONES DEL FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoUsuario');

        const nombre = $('[name="nombreUsuario"]').val().trim();
        const contrasena = $('[name="contrasena"]').val();
        const idTipo = $('[name="idTipoUsuario"]').val();
        const idEmpleado = $('[name="idEmpleado"]').val();

        if (nombre === '') {
            mostrarError('nombreUsuario', 'El nombre de usuario es obligatorio.');
            esValido = false;
        } else if (!validarNombreUsuario(nombre)) {
            mostrarError('nombreUsuario', 'Use solo letras, números, puntos, guiones y guiones bajos (3-50 caracteres).');
            esValido = false;
        }

        if (contrasena === '') {
            mostrarError('contrasena', 'La contraseña es obligatoria.');
            esValido = false;
        } else if (!validarContrasena(contrasena)) {
            mostrarError('contrasena', 'La contraseña debe tener al menos 6 caracteres.');
            esValido = false;
        }

        if (!idTipo || idTipo === '') {
            mostrarError('idTipoUsuario', 'Debe seleccionar un tipo de usuario.');
            esValido = false;
        }

        if (!idEmpleado || idEmpleado === '') {
            mostrarError('idEmpleado', 'Debe seleccionar un empleado.');
            esValido = false;
        }

        return esValido;
    }

    // VALIDACIONES DEL FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarUsuario');

        const nombre = $('#nombreUsuarioEdit').val().trim();
        const contrasena = $('#contrasenaEdit').val();
        const idTipo = $('#idTipoUsuarioEdit').val();
        const idEmpleado = $('#idEmpleadoEdit').val();

        if (nombre === '') {
            mostrarError('nombreUsuarioEdit', 'El nombre de usuario es obligatorio.');
            esValido = false;
        } else if (!validarNombreUsuario(nombre)) {
            mostrarError('nombreUsuarioEdit', 'Use solo letras, números, puntos, guiones y guiones bajos (3-50 caracteres).');
            esValido = false;
        }

        if (contrasena !== '' && !validarContrasena(contrasena, false)) {
            mostrarError('contrasenaEdit', 'La contraseña debe tener al menos 6 caracteres.');
            esValido = false;
        }

        if (!idTipo || idTipo === '') {
            mostrarError('idTipoUsuarioEdit', 'Debe seleccionar un tipo de usuario.');
            esValido = false;
        }

        if (!idEmpleado || idEmpleado === '') {
            mostrarError('idEmpleadoEdit', 'Debe seleccionar un empleado.');
            esValido = false;
        }

        return esValido;
    }

    // LIMPIAR ERRORES AL CERRAR MODALES
    $('#modalUsuario').on('hidden.bs.modal', function() {
        $('#formNuevoUsuario')[0].reset();
        $('#buscarEmpleado').val('');
        $('#idEmpleado option').show();
        $('#empleadoInfo').hide();
        limpiarErrores('formNuevoUsuario');
    });

    $('#modalEditarUsuario').on('hidden.bs.modal', function() {
        $('#buscarEmpleadoEdit').val('');
        $('#idEmpleadoEdit option').show();
        $('#empleadoInfoEdit').hide();
        limpiarErrores('formEditarUsuario');
    });

    // GUARDAR NUEVO USUARIO
    $('#formNuevoUsuario').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const nombreTipo = $('select[name="idTipoUsuario"] option:selected').text();
        const nombreEmpleado = $('select[name="idEmpleado"] option:selected').text();

        $.ajax({
            url: 'index.php?action=guardarUsuario',
            type: 'POST', 
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalUsuario').modal('hide');
                    $('#formNuevoUsuario')[0].reset();
                    $('#buscarEmpleado').val('');
                    $('#idEmpleado option').show();
                    $('#empleadoInfo').hide();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="6"]').length > 0) {
                        $('table tbody tr td[colspan="6"]').closest('tr').remove();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.nombreUsuario}</span></td>
                            <td><span class="text-muted small">${nombreTipo}</span></td>
                            <td><span class="text-muted small">${nombreEmpleado}</span></td>
                            <td><span class="text-muted small">${nombreEmpleado.split(' - ')[0]}</span></td>
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
        const idUsuario = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarUsuario&id=' + idUsuario,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idUsuarioEdit').val(data.idUsuario);
                    $('#nombreUsuarioEdit').val(data.nombreUsuario);
                    $('#contrasenaEdit').val('');
                    $('#idTipoUsuarioEdit').val(data.idTipoUsuario);
                    $('#idEmpleadoEdit').val(data.idEmpleado);

                    // Mostrar info del empleado seleccionado
                    const empleadoOption = $('#idEmpleadoEdit option[value="' + data.idEmpleado + '"]');
                    if (empleadoOption.length) {
                        $('#empleadoSeleccionadoTextoEdit').text(empleadoOption.text());
                        $('#empleadoInfoEdit').show();
                    }

                    limpiarErrores('formEditarUsuario');
                    $('#modalEditarUsuario').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del usuario.", "danger");
            }
        });
    });

    // ACTUALIZAR USUARIO
    $('#formEditarUsuario').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idUsuarioEdit').val();
        const nuevoNombre = $('#nombreUsuarioEdit').val();
        const nuevoTipo = $('#idTipoUsuarioEdit option:selected').text();
        const nuevoEmpleado = $('#idEmpleadoEdit option:selected').text();

        $.ajax({
            url: 'index.php?action=editarUsuario', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarUsuario').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNombre);
                    fila.find('td:nth-child(3) span').text(nuevoTipo);
                    fila.find('td:nth-child(4) span').text(nuevoEmpleado);
                    fila.find('td:nth-child(5) span').text(nuevoEmpleado.split(' - ')[0]);

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

    // ELIMINAR USUARIO
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idUsuario = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este usuario?')) {
            $.ajax({
                url: 'index.php?action=eliminarUsuario',
                type: 'POST',
                data: { idUsuario: idUsuario },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");

                        fila.fadeOut(600, function() {
                            $(this).remove();

                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay usuarios registrados actualmente.</td></tr>');
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