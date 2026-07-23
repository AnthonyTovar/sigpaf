$(document).ready(function() {

    // ============================================================
    // FUNCIONES GLOBALES
    // ============================================================
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

        alerta.css({
            'display': 'none',
            'position': 'fixed',
            'top': '20px',
            'left': '50%',
            'transform': 'translateX(-50%)',
            'z-index': '9999',
            'min-width': '350px',
            'text-align': 'center',
            'box-shadow': '0 4px 12px rgba(0,0,0,0.15)'
        });

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

    // ============================================================
    // VALIDACIÓN: NO MÁS DE 3 LETRAS REPETIDAS CONSECUTIVAS
    // ============================================================
    function tieneLetrasRepetidas(texto) {
        const textoSinEspacios = texto.replace(/\s/g, '');
        return /(.)\1{3,}/i.test(textoSinEspacios);
    }

    // ============================================================
    // VIBRACIÓN DEL INPUT
    // ============================================================
    function vibrarInput(elemento) {
        $(elemento).addClass('vibrar-input');
        setTimeout(function() {
            $(elemento).removeClass('vibrar-input');
        }, 300);
    }

    // ============================================================
    // BLOQUEAR ESCRITURA SI HAY 3 LETRAS REPETIDAS (KEYDOWN)
    // ============================================================
    function bloquearRepetidas(e, input) {
        const valorActual = $(input).val();
        const tecla = e.key;

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

        if (!tieneLetrasRepetidas(valorActual)) {
            $(input).removeClass('is-invalid');
            $(`#error-${$(input).attr('name')}`).text('').hide();
        }

        return true;
    }

    // ============================================================
    // BLOQUEAR ESCRITURA SI HAY 3 LETRAS REPETIDAS (INPUT/PASTE)
    // ============================================================
    function validarInputEnTiempoReal(input) {
        const valor = $(input).val();

        if (tieneLetrasRepetidas(valor)) {
            $(input).val(valor.slice(0, -1));
            vibrarInput(input);
            mostrarError($(input).attr('name'), 'No puede haber letras repetidas más de 3 veces consecutivas.');
        } else {
            $(input).removeClass('is-invalid');
            $(`#error-${$(input).attr('name')}`).text('').hide();
        }
    }

    // ============================================================
    // BLOQUEAR/DESBLOQUEAR CAMPOS DEL FORMULARIO NUEVO
    // ============================================================
    function bloquearCamposRegistro() {
        $('#nombreUsuario, #contrasena, #confirmarContrasena, #idTipoUsuario').prop('disabled', true);
        $('#btnGuardarUsuario').prop('disabled', true);
        $('#nombreUsuario, #contrasena, #confirmarContrasena, #idTipoUsuario').closest('.col-md-6').css('opacity', '0.5');
    }

    function desbloquearCamposRegistro() {
        $('#nombreUsuario, #contrasena, #confirmarContrasena, #idTipoUsuario').prop('disabled', false);
        $('#btnGuardarUsuario').prop('disabled', false);
        $('#nombreUsuario, #contrasena, #confirmarContrasena, #idTipoUsuario').closest('.col-md-6').css('opacity', '1');
    }

    bloquearCamposRegistro();

    // ============================================================
    // BUSCAR EMPLEADO POR CÉDULA (MODAL NUEVO)
    // ============================================================
    $('#btnBuscarEmpleado').on('click', function() {
        const cedula = $('#buscarCedulaEmpleado').val().trim();
        const btn = $(this);

        if (cedula === '') {
            lanzarAviso('Ingrese una cédula para buscar.', 'warning');
            $('#buscarCedulaEmpleado').addClass('is-invalid');
            return;
        }

        $('#buscarCedulaEmpleado').removeClass('is-invalid');
        $('#resultadoBusquedaEmpleado').hide();
        bloquearCamposRegistro();

        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Buscando...');

        $.ajax({
            url: 'index.php?action=buscarEmpleadoPorCedula',
            type: 'GET',
            data: { cedula: cedula },
            dataType: 'json',
            success: function(response) {
                $('#resultadoBusquedaEmpleado').show();

                if (response.status === 'success') {
                    $('#idEmpleado').val(response.idEmpleado);
                    $('#nombreEmpleadoEncontrado').text(response.nombres + ' ' + response.apellidos);
                    $('#cedulaEmpleadoEncontrado').text(response.cedulaEmpleado);
                    $('#infoEmpleadoEncontrado').removeClass('d-none');
                    $('#infoEmpleadoYaTiene').addClass('d-none');
                    $('#infoEmpleadoNoExiste').addClass('d-none');
                    desbloquearCamposRegistro();
                    lanzarAviso('Empleado encontrado. Puede registrar el usuario.', 'success');
                } else if (response.status === 'ya_tiene_usuario') {
                    $('#idEmpleado').val('');
                    $('#nombreEmpleadoYaTiene').text(response.nombres + ' ' + response.apellidos);
                    $('#usuarioAsignado').text(response.idUsuario);
                    $('#infoEmpleadoEncontrado').addClass('d-none');
                    $('#infoEmpleadoYaTiene').removeClass('d-none');
                    $('#infoEmpleadoNoExiste').addClass('d-none');
                    bloquearCamposRegistro();
                    lanzarAviso('Este empleado ya tiene un usuario asignado.', 'danger');
                } else {
                    $('#idEmpleado').val('');
                    $('#cedulaNoExiste').text(cedula);
                    $('#infoEmpleadoEncontrado').addClass('d-none');
                    $('#infoEmpleadoYaTiene').addClass('d-none');
                    $('#infoEmpleadoNoExiste').removeClass('d-none');
                    bloquearCamposRegistro();
                    lanzarAviso(response.message || 'No se encontró un empleado con esa cédula.', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', status, error);
                lanzarAviso('Error al buscar el empleado. Verifique la conexión.', 'danger');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-search me-1"></i> Buscar');
            }
        });
    });

    $('#buscarCedulaEmpleado').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnBuscarEmpleado').click();
        }
    });

    // ============================================================
    // VALIDAR NOMBRE DE USUARIO
    // ============================================================
    function validarNombreUsuario(nombre) {
        return /^[a-zA-Z0-9_.-]{3,50}$/.test(nombre);
    }

    // ============================================================
    // VALIDAR CONTRASEÑA
    // ============================================================
    function validarContrasena(contrasena, requerido = true) {
        if (!requerido && contrasena === '') return true;
        return contrasena.length >= 6 && contrasena.length <= 255;
    }

    // ============================================================
    // VALIDAR CONFIRMAR CONTRASEÑA EN TIEMPO REAL (NUEVO)
    // ============================================================
    function validarConfirmarContrasenaNuevo() {
        const contrasena = $('#contrasena').val();
        const confirmar = $('#confirmarContrasena').val();

        if (confirmar === '') {
            $('#confirmarContrasena').removeClass('is-invalid');
            $('#error-confirmarContrasena').text('').hide();
            return true;
        }
        if (contrasena !== confirmar) {
            $('#confirmarContrasena').addClass('is-invalid');
            $('#error-confirmarContrasena').text('Las contraseñas no coinciden.').show();
            return false;
        } else {
            $('#confirmarContrasena').removeClass('is-invalid');
            $('#error-confirmarContrasena').text('').hide();
            return true;
        }
    }

    $('#confirmarContrasena').on('input', function() {
        validarConfirmarContrasenaNuevo();
    });
    $('#contrasena').on('input', function() {
        if ($('#confirmarContrasena').val() !== '') {
            validarConfirmarContrasenaNuevo();
        }
    });

    // ============================================================
    // VALIDAR CONFIRMAR CONTRASEÑA EN TIEMPO REAL (EDITAR)
    // ============================================================
    function validarConfirmarContrasenaEditar() {
        const contrasena = $('#contrasenaEdit').val();
        const confirmar = $('#confirmarContrasenaEdit').val();

        if (confirmar === '' && contrasena === '') {
            $('#confirmarContrasenaEdit').removeClass('is-invalid');
            $('#error-confirmarContrasenaEdit').text('').hide();
            return true;
        }
        if (contrasena === '' && confirmar !== '') {
            $('#confirmarContrasenaEdit').addClass('is-invalid');
            $('#error-confirmarContrasenaEdit').text('Primero ingrese la nueva contraseña.').show();
            return false;
        }
        if (contrasena !== confirmar) {
            $('#confirmarContrasenaEdit').addClass('is-invalid');
            $('#error-confirmarContrasenaEdit').text('Las contraseñas no coinciden.').show();
            return false;
        } else {
            $('#confirmarContrasenaEdit').removeClass('is-invalid');
            $('#error-confirmarContrasenaEdit').text('').hide();
            return true;
        }
    }

    $('#confirmarContrasenaEdit').on('input', function() {
        validarConfirmarContrasenaEditar();
    });
    $('#contrasenaEdit').on('input', function() {
        if ($('#confirmarContrasenaEdit').val() !== '') {
            validarConfirmarContrasenaEditar();
        }
    });

    // ============================================================
    // EVENTOS KEYDOWN E INPUT PARA BLOQUEAR LETRAS REPETIDAS
    // ============================================================
    $('#nombreUsuario').on('keydown', function(e) {
        return bloquearRepetidas(e, this);
    });
    $('#nombreUsuario').on('input', function() {
        validarInputEnTiempoReal(this);
    });

    $('#nombreUsuarioEdit').on('keydown', function(e) {
        return bloquearRepetidas(e, this);
    });
    $('#nombreUsuarioEdit').on('input', function() {
        validarInputEnTiempoReal(this);
    });

    // ============================================================
    // VALIDACIONES DEL FORMULARIO NUEVO
    // ============================================================
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoUsuario');

        const idEmpleado = $('#idEmpleado').val();
        const nombre = $('#nombreUsuario').val().trim();
        const contrasena = $('#contrasena').val();
        const confirmar = $('#confirmarContrasena').val();
        const idTipo = $('#idTipoUsuario').val();

        if (!idEmpleado || idEmpleado === '') {
            lanzarAviso('Debe buscar y seleccionar un empleado primero.', 'warning');
            esValido = false;
        }

        if (nombre === '') {
            mostrarError('nombreUsuario', 'El nombre de usuario es obligatorio.');
            esValido = false;
        } else if (!validarNombreUsuario(nombre)) {
            mostrarError('nombreUsuario', 'Use solo letras, números, puntos, guiones y guiones bajos (3-50 caracteres).');
            esValido = false;
        } else if (tieneLetrasRepetidas(nombre)) {
            mostrarError('nombreUsuario', 'No puede haber letras repetidas más de 3 veces consecutivas.');
            esValido = false;
        }

        if (contrasena === '') {
            mostrarError('contrasena', 'La contraseña es obligatoria.');
            esValido = false;
        } else if (!validarContrasena(contrasena)) {
            mostrarError('contrasena', 'La contraseña debe tener al menos 6 caracteres.');
            esValido = false;
        }

        if (confirmar === '') {
            mostrarError('confirmarContrasena', 'Debe confirmar la contraseña.');
            esValido = false;
        } else if (contrasena !== confirmar) {
            mostrarError('confirmarContrasena', 'Las contraseñas no coinciden.');
            esValido = false;
        }

        if (!idTipo || idTipo === '') {
            mostrarError('idTipoUsuario', 'Debe seleccionar un tipo de usuario.');
            esValido = false;
        }

        return esValido;
    }

    // ============================================================
    // VALIDACIONES DEL FORMULARIO EDITAR
    // ============================================================
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarUsuario');

        const nombre = $('#nombreUsuarioEdit').val().trim();
        const contrasena = $('#contrasenaEdit').val();
        const confirmar = $('#confirmarContrasenaEdit').val();
        const idTipo = $('#idTipoUsuarioEdit').val();

        if (nombre === '') {
            mostrarError('nombreUsuarioEdit', 'El nombre de usuario es obligatorio.');
            esValido = false;
        } else if (!validarNombreUsuario(nombre)) {
            mostrarError('nombreUsuarioEdit', 'Use solo letras, números, puntos, guiones y guiones bajos (3-50 caracteres).');
            esValido = false;
        } else if (tieneLetrasRepetidas(nombre)) {
            mostrarError('nombreUsuarioEdit', 'No puede haber letras repetidas más de 3 veces consecutivas.');
            esValido = false;
        }

        if (contrasena !== '' && !validarContrasena(contrasena, false)) {
            mostrarError('contrasenaEdit', 'La contraseña debe tener al menos 6 caracteres.');
            esValido = false;
        }

        if (contrasena !== '' && confirmar === '') {
            mostrarError('confirmarContrasenaEdit', 'Debe confirmar la nueva contraseña.');
            esValido = false;
        } else if (contrasena !== '' && contrasena !== confirmar) {
            mostrarError('confirmarContrasenaEdit', 'Las contraseñas no coinciden.');
            esValido = false;
        }

        if (!idTipo || idTipo === '') {
            mostrarError('idTipoUsuarioEdit', 'Debe seleccionar un tipo de usuario.');
            esValido = false;
        }

        return esValido;
    }

    // ============================================================
    // LIMPIAR ERRORES AL CERRAR MODALES
    // ============================================================
    $('#modalUsuario').on('hidden.bs.modal', function() {
        $('#formNuevoUsuario')[0].reset();
        $('#idEmpleado').val('');
        $('#resultadoBusquedaEmpleado').hide();
        $('#infoEmpleadoEncontrado').addClass('d-none');
        $('#infoEmpleadoYaTiene').addClass('d-none');
        $('#infoEmpleadoNoExiste').addClass('d-none');
        bloquearCamposRegistro();
        limpiarErrores('formNuevoUsuario');
    });

    $('#modalEditarUsuario').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarUsuario');
    });

    // ============================================================
    // GUARDAR NUEVO USUARIO
    // ============================================================
    $('#formNuevoUsuario').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const nombreTipo = $('select[name="idTipoUsuario"] option:selected').text();
        const nombreEmpleado = $('#nombreEmpleadoEncontrado').text();
        const cedulaEmpleado = $('#cedulaEmpleadoEncontrado').text();

        $.ajax({
            url: 'index.php?action=guardarUsuario',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalUsuario').modal('hide');
                    $('#formNuevoUsuario')[0].reset();
                    $('#idEmpleado').val('');
                    $('#resultadoBusquedaEmpleado').hide();
                    bloquearCamposRegistro();
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
                            <td><span class="text-muted small">${cedulaEmpleado}</span></td>
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

    // ============================================================
    // CARGAR DATOS EN MODAL DE EDICIÓN
    // ============================================================
    $(document).on('click', '.btn-editar', function() {
        const idUsuario = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarUsuario&id=' + idUsuario,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idUsuarioEdit').val(data.idUsuario);
                    $('#idEmpleadoEdit').val(data.idEmpleado);
                    $('#nombreUsuarioEdit').val(data.nombreUsuario);
                    $('#contrasenaEdit').val('');
                    $('#confirmarContrasenaEdit').val('');
                    $('#idTipoUsuarioEdit').val(data.idTipoUsuario);

                    // MOSTRAR PERFIL DEL EMPLEADO (solo lectura)
                    const nombreCompleto = (data.nombres || '') + ' ' + (data.apellidos || '');
                    $('#nombreEmpleadoEdit').text(nombreCompleto.trim() || 'N/A');
                    $('#cedulaEmpleadoEdit').text(data.cedulaEmpleado || 'N/A');

                    limpiarErrores('formEditarUsuario');
                    $('#modalEditarUsuario').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del usuario.", "danger");
            }
        });
    });

    // ============================================================
    // ACTUALIZAR USUARIO
    // ============================================================
    $('#formEditarUsuario').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idUsuarioEdit').val();
        const nuevoNombre = $('#nombreUsuarioEdit').val();
        const nuevoTipo = $('#idTipoUsuarioEdit option:selected').text();
        const nombreEmpleado = $('#nombreEmpleadoEdit').text();
        const cedulaEmpleado = $('#cedulaEmpleadoEdit').text();

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
                    fila.find('td:nth-child(4) span').text(nombreEmpleado);
                    fila.find('td:nth-child(5) span').text(cedulaEmpleado);

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

    // ============================================================
    // ELIMINAR USUARIO
    // ============================================================
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