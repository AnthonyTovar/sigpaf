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
        $(`#${formId} input`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    // ═══════════════════════════════════════════════
    // VALIDAR CÉDULA VENEZOLANA
    // ═══════════════════════════════════════════════
    function validarCedula(cedula) {
        return /^[VEve]\d{6,9}$/.test(cedula.replace(/-/g, ''));
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

        const cedula = $('[name="cedDocente"]').val().trim();
        const nombres = $('[name="nombreDocente"]').val().trim();
        const apellidos = $('[name="apellidoDocente"]').val().trim();
        const telefono = $('[name="telfDocente"]').val().trim();

        if (cedula === '') {
            mostrarError('cedDocente', 'La cédula es obligatoria.');
            esValido = false;
        } else if (!validarCedula(cedula)) {
            mostrarError('cedDocente', 'Formato inválido. Use V12345678 o E12345678.');
            esValido = false;
        } else if (cedula.replace(/-/g, '').length > 10) {
            mostrarError('cedDocente', 'La cédula no puede exceder 9 dígitos más la letra.');
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
        } else if (!/^\d{4}-\d{7}$/.test(telefono)) {
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

        const cedula = $('#cedDocenteEdit').val().trim();
        const nombres = $('#nombreDocenteEdit').val().trim();
        const apellidos = $('#apellidoDocenteEdit').val().trim();
        const telefono = $('#telfDocenteEdit').val().trim();

        if (cedula === '') {
            mostrarError('cedDocenteEdit', 'La cédula es obligatoria.');
            esValido = false;
        } else if (!validarCedula(cedula)) {
            mostrarError('cedDocenteEdit', 'Formato inválido. Use V12345678 o E12345678.');
            esValido = false;
        } else if (cedula.replace(/-/g, '').length > 10) {
            mostrarError('cedDocenteEdit', 'La cédula no puede exceder 9 dígitos más la letra.');
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
        } else if (!/^\d{4}-\d{7}$/.test(telefono)) {
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
        limpiarErrores('formNuevoDocente');
    });

    $('#modalEditarDocente').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarDocente');
    });

    // ═══════════════════════════════════════════════
    // GUARDAR NUEVO DOCENTE
    // ═══════════════════════════════════════════════
    $('#formNuevoDocente').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        $.ajax({
            url: 'index.php?action=guardarDocente',
            type: 'POST', 
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalDocente').modal('hide');
                    $('#formNuevoDocente')[0].reset();
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
            error: function() {
                lanzarAviso("Error al procesar el registro", "danger");
            }
        });
    });

    // ═══════════════════════════════════════════════
    // CARGAR DATOS EN MODAL DE EDICIÓN
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
                    $('#cedDocenteEdit').val(data.cedDocente);
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
    // ACTUALIZAR DOCENTE
    // ═══════════════════════════════════════════════
    $('#formEditarDocente').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idDocenteEdit').val();
        const nuevaCedula = $('#cedDocenteEdit').val();
        const nuevoNombre = $('#nombreDocenteEdit').val();
        const nuevoApellido = $('#apellidoDocenteEdit').val();
        const nuevoTelefono = $('#telfDocenteEdit').val() || 'N/A';

        $.ajax({
            url: 'index.php?action=editarDocente', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarDocente').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    
                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2)').text(nuevaCedula);
                    fila.find('td:nth-child(3) span').text(nuevoNombre);
                    fila.find('td:nth-child(4) span').text(nuevoApellido);
                    fila.find('td:nth-child(5)').text(nuevoTelefono);
                    
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