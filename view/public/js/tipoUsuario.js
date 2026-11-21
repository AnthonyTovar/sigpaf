// Gestión de tipos de usuario

$(document).ready(function() {

    // Función global
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

    // Validaciones del formulario nuevo
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoTipoUsuario');

        const rol = $('[name="rolUsuario"]').val().trim();

        if (rol === '') {
            mostrarError('rolUsuario', 'El rol de usuario es obligatorio.');
            esValido = false;
        } else if (rol.length < 2) {
            mostrarError('rolUsuario', 'El rol debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (rol.length > 15) {
            mostrarError('rolUsuario', 'El rol no puede exceder los 15 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(rol)) {
            mostrarError('rolUsuario', 'El rol solo puede contener letras y espacios.');
            esValido = false;
        }

        return esValido;
    }

    // Validaciones del formulario editar
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarTipoUsuario');

        const rol = $('#rolUsuarioEdit').val().trim();

        if (rol === '') {
            mostrarError('rolUsuarioEdit', 'El rol de usuario es obligatorio.');
            esValido = false;
        } else if (rol.length < 2) {
            mostrarError('rolUsuarioEdit', 'El rol debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (rol.length > 15) {
            mostrarError('rolUsuarioEdit', 'El rol no puede exceder los 15 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(rol)) {
            mostrarError('rolUsuarioEdit', 'El rol solo puede contener letras y espacios.');
            esValido = false;
        }

        return esValido;
    }

    // Limpiar errores al cerrar modales
    $('#modalTipoUsuario').on('hidden.bs.modal', function() {
        $('#formNuevoTipoUsuario')[0].reset();
        limpiarErrores('formNuevoTipoUsuario');
    });

    $('#modalEditarTipoUsuario').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarTipoUsuario');
    });

    // Guardar nuevo tipo de usuario
    $('#formNuevoTipoUsuario').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        $.ajax({
            url: 'index.php?action=guardarTipoUsuario',
            type: 'POST', 
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalTipoUsuario').modal('hide');
                    $('#formNuevoTipoUsuario')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="3"]').length > 0) {
                        $('table tbody tr td[colspan="3"]').closest('tr').remove();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td><span class="fw-bold text-dark">${response.rol}</span></td>
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

    // Cargar datos en modal de edición
    $(document).on('click', '.btn-editar', function() {
        const idTipoUsuario = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarTipoUsuario&id=' + idTipoUsuario,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idTipoUsuarioEdit').val(data.idTipoUsuario);
                    $('#rolUsuarioEdit').val(data.rolUsuario);
                    limpiarErrores('formEditarTipoUsuario');
                    $('#modalEditarTipoUsuario').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del tipo de usuario.", "danger");
            }
        });
    });

    // Actualizar tipo de usuario
    $('#formEditarTipoUsuario').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idTipoUsuarioEdit').val();
        const nuevoRol = $('#rolUsuarioEdit').val();

        $.ajax({
            url: 'index.php?action=editarTipoUsuario', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarTipoUsuario').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoRol);

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

    // Eliminar tipo de usuario
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idTipoUsuario = $(this).data('id');
        const fila = $(this).closest('tr');

        Validar.confirmar('¿Estás seguro de eliminar este tipo de usuario?', function() {
            $.ajax({
                url: 'index.php?action=eliminarTipoUsuario',
                type: 'POST',
                data: { idTipoUsuario: idTipoUsuario },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");

                        fila.fadeOut(600, function() {
                            $(this).remove();

                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="3" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay tipos de usuario registrados actualmente.</td></tr>');
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