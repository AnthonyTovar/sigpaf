$(document).ready(function() {

    // ═══════════════════════════════════════════════
    // FUNCIÓN GLOBAL DE ALERTAS
    // ═══════════════════════════════════════════════
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

    // ═══════════════════════════════════════════════
    // MOSTRAR / LIMPIAR ERRORES
    // ═══════════════════════════════════════════════
    function mostrarError(campo, mensaje) {
        const input = $(`[name="${campo}"]`);
        const errorDiv = $(`#error-${campo}`);

        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).show();
        }
    }

    function limpiarErrores(formId) {
        $(`#${formId} input, #${formId} select`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    // ═══════════════════════════════════════════════
    // EXTRAER PREFIJO Y NÚMERO DE CÉDULA
    // ═══════════════════════════════════════════════
    function extraerNumeroCedula(cedulaCompleta) {
        if (!cedulaCompleta) return '';
        return cedulaCompleta.replace(/^[VE]/i, '');
    }

    function extraerPrefijoCedula(cedulaCompleta) {
        if (!cedulaCompleta) return 'V';
        const match = cedulaCompleta.match(/^([VE])/i);
        return match ? match[1].toUpperCase() : 'V';
    }

    // ═══════════════════════════════════════════════
    // VALIDAR NÚMERO DE CÉDULA (solo numérica)
    // ═══════════════════════════════════════════════
    function validarNumeroCedula(numero) {
        return /^\d{6,9}$/.test(numero.trim());
    }

    // ═══════════════════════════════════════════════
    // VALIDAR TELÉFONO
    // ═══════════════════════════════════════════════
    function validarTelefono(telefono) {
        if (!telefono) return true;
        return /^\d{4}-\d{7}$/.test(telefono);
    }

    // ═══════════════════════════════════════════════
    // VALIDAR FORMULARIO NUEVO
    // ═══════════════════════════════════════════════
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoDocente');

        const nacionalidad = $('#nacionalidad').val();
        const numeroCedula = $('#cedDocente').val().trim();
        const nombres = $('[name="nombreDocente"]').val().trim();
        const apellidos = $('[name="apellidoDocente"]').val().trim();
        const telefono = $('[name="telfDocente"]').val().trim();

        if (!nacionalidad) {
            mostrarError('nacionalidad', 'Debe seleccionar una nacionalidad.');
            esValido = false;
        }

        if (numeroCedula === '') {
            mostrarError('cedDocente', 'La cédula es obligatoria.');
            esValido = false;
        } else if (!validarNumeroCedula(numeroCedula)) {
            mostrarError('cedDocente', 'Debe contener entre 6 y 9 dígitos numéricos.');
            esValido = false;
        }

        if (nombres === '') {
            mostrarError('nombreDocente', 'Los nombres son obligatorios.');
            esValido = false;
        } else if (nombres.length < 2) {
            mostrarError('nombreDocente', 'Los nombres deben tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombres.length > 50) {
            mostrarError('nombreDocente', 'Los nombres no pueden exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombres)) {
            mostrarError('nombreDocente', 'Los nombres solo pueden contener letras y espacios.');
            esValido = false;
        }

        if (apellidos === '') {
            mostrarError('apellidoDocente', 'Los apellidos son obligatorios.');
            esValido = false;
        } else if (apellidos.length < 2) {
            mostrarError('apellidoDocente', 'Los apellidos deben tener al menos 2 caracteres.');
            esValido = false;
        } else if (apellidos.length > 50) {
            mostrarError('apellidoDocente', 'Los apellidos no pueden exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(apellidos)) {
            mostrarError('apellidoDocente', 'Los apellidos solo pueden contener letras y espacios.');
            esValido = false;
        }

        if (telefono === '') {
            mostrarError('telfDocente', 'El teléfono no puede estar vacío.');
            esValido = false;
        } else if (telefono.length > 12) {
            mostrarError('telfDocente', 'El teléfono no puede exceder los 12 caracteres.');
            esValido = false;
        } else if (!validarTelefono(telefono)) {
            mostrarError('telfDocente', 'Formato del telefono Invalido. Ejemplo: 0424-5555555');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // VALIDAR FORMULARIO EDITAR
    // ═══════════════════════════════════════════════
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarDocente');

        const nacionalidad = $('#nacionalidadEdit').val();
        const numeroCedula = $('#cedDocenteEdit').val().trim();
        const nombres = $('#nombreDocenteEdit').val().trim();
        const apellidos = $('#apellidoDocenteEdit').val().trim();
        const telefono = $('#telfDocenteEdit').val().trim();

        if (!nacionalidad) {
            mostrarError('nacionalidadEdit', 'Debe seleccionar una nacionalidad.');
            esValido = false;
        }

        if (numeroCedula === '') {
            mostrarError('cedDocenteEdit', 'La cédula es obligatoria.');
            esValido = false;
        } else if (!validarNumeroCedula(numeroCedula)) {
            mostrarError('cedDocenteEdit', 'Debe contener entre 6 y 9 dígitos numéricos.');
            esValido = false;
        }

        if (nombres === '') {
            mostrarError('nombreDocenteEdit', 'Los nombres son obligatorios.');
            esValido = false;
        } else if (nombres.length < 2) {
            mostrarError('nombreDocenteEdit', 'Los nombres deben tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombres.length > 50) {
            mostrarError('nombreDocenteEdit', 'Los nombres no pueden exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombres)) {
            mostrarError('nombreDocenteEdit', 'Los nombres solo pueden contener letras y espacios.');
            esValido = false;
        }

        if (apellidos === '') {
            mostrarError('apellidoDocenteEdit', 'Los apellidos son obligatorios.');
            esValido = false;
        } else if (apellidos.length < 2) {
            mostrarError('apellidoDocenteEdit', 'Los apellidos deben tener al menos 2 caracteres.');
            esValido = false;
        } else if (apellidos.length > 50) {
            mostrarError('apellidoDocenteEdit', 'Los apellidos no pueden exceder los 50 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(apellidos)) {
            mostrarError('apellidoDocenteEdit', 'Los apellidos solo pueden contener letras y espacios.');
            esValido = false;
        }

        if (telefono === '') {
            mostrarError('telfDocenteEdit', 'El teléfono no puede estar vacío.');
            esValido = false;
        } else if (telefono.length > 12) {
            mostrarError('telfDocenteEdit', 'El teléfono no puede exceder los 12 caracteres.');
            esValido = false;
        } else if (!validarTelefono(telefono)) {
            mostrarError('telfDocenteEdit', 'Formato del telefono Invalido. Ejemplo: 0424-5555555');
            esValido = false;
        }

        return esValido;
    }

    // ═══════════════════════════════════════════════
    // LIMPIAR AL CERRAR MODALES
    // ═══════════════════════════════════════════════
    $('#modalDocente').on('hidden.bs.modal', function() {
        $('#formNuevoDocente')[0].reset();
        $('#nacionalidad').val('V');
        limpiarErrores('formNuevoDocente');
    });

    $('#modalEditarDocente').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarDocente');
    });

    // ═══════════════════════════════════════════════
    // CORREGIDO: GUARDAR NUEVO DOCENTE
    // ═══════════════════════════════════════════════
    $('#formNuevoDocente').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        // Construir cédula completa: prefijo + número
        const nacionalidad = $('#nacionalidad').val();
        const numeroCedula = $('#cedDocente').val().trim();
        const cedulaCompleta = nacionalidad + numeroCedula;

        // Construir datos manualmente
        const formData = {
            nacionalidad: nacionalidad,
            cedDocente: cedulaCompleta,
            nombreDocente: $('[name="nombreDocente"]').val().trim(),
            apellidoDocente: $('[name="apellidoDocente"]').val().trim(),
            telfDocente: $('[name="telfDocente"]').val().trim()
        };

        $.ajax({
            url: 'index.php?action=guardarDocente',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalDocente').modal('hide');
                    $('#formNuevoDocente')[0].reset();
                    $('#nacionalidad').val('V');
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="6"]').length > 0) {
                        $('table tbody tr td[colspan="6"]').closest('tr').remove();
                    }

                    const telefono = response.telefono || 'N/A';

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td>${response.cedula}</td>
                            <td class="fw-bold text-dark"><span>${response.nombres}</span></td>
                            <td class="fw-bold text-dark"><span>${response.apellidos}</span></td>
                            <td class="text-muted small">${telefono}</td>
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
            error: function(xhr, status, error) {
                console.error('Error AJAX:', status, error);
                console.error('Response:', xhr.responseText);
                lanzarAviso("Error al procesar el registro. Verifique la consola para más detalles.", "danger");
            }
        });
    });

    // ═══════════════════════════════════════════════
    // CORREGIDO: CARGAR DATOS EN MODAL DE EDICIÓN
    // ═══════════════════════════════════════════════
    $(document).on('click', '.btn-editar', function() {
        const idDocente = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarDocente&id=' + idDocente,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idDocenteEdit').val(data.idDocente);
                    // Separar prefijo y número de la cédula guardada
                    const prefijo = extraerPrefijoCedula(data.cedDocente);
                    const numero = extraerNumeroCedula(data.cedDocente);
                    $('#nacionalidadEdit').val(prefijo);
                    $('#cedDocenteEdit').val(numero);
                    $('#nombreDocenteEdit').val(data.nombreDocente);
                    $('#apellidoDocenteEdit').val(data.apellidoDocente);
                    $('#telfDocenteEdit').val(data.telfDocente);
                    limpiarErrores('formEditarDocente');
                    $('#modalEditarDocente').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del docente.", "danger");
            }
        });
    });

    // ═══════════════════════════════════════════════
    // CORREGIDO: ACTUALIZAR DOCENTE
    // ═══════════════════════════════════════════════
    $('#formEditarDocente').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idDocenteEdit').val();
        const nacionalidad = $('#nacionalidadEdit').val();
        const numeroCedula = $('#cedDocenteEdit').val().trim();
        const cedulaCompleta = nacionalidad + numeroCedula;
        const nuevoNombre = $('#nombreDocenteEdit').val();
        const nuevoApellido = $('#apellidoDocenteEdit').val();
        const nuevoTelefono = $('#telfDocenteEdit').val() || 'N/A';

        // Construir datos manualmente
        const formData = {
            idDocenteEdit: idActualizado,
            nacionalidadEdit: nacionalidad,
            cedDocenteEdit: cedulaCompleta,
            nombreDocenteEdit: nuevoNombre,
            apellidoDocenteEdit: nuevoApellido,
            telfDocenteEdit: $('#telfDocenteEdit').val().trim()
        };

        $.ajax({
            url: 'index.php?action=editarDocente',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarDocente').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2)').text(cedulaCompleta);
                    fila.find('td:nth-child(3) span').text(nuevoNombre);
                    fila.find('td:nth-child(4) span').text(nuevoApellido);
                    fila.find('td:nth-child(5)').text(nuevoTelefono);

                    fila.fadeOut(100).fadeIn(800);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', status, error);
                console.error('Response:', xhr.responseText);
                lanzarAviso("Error al actualizar el registro. Verifique la consola para más detalles.", "danger");
            }
        });
    });

    // ═══════════════════════════════════════════════
    // ELIMINAR DOCENTE
    // ═══════════════════════════════════════════════
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        const idDocente = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este docente?')) {
            $.ajax({
                url: 'index.php?action=eliminarDocente',
                type: 'POST',
                data: { idDocente: idDocente },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");

                        fila.fadeOut(600, function() {
                            $(this).remove();

                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay docentes registrados actualmente.</td></tr>');
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