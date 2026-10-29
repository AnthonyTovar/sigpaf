// Gestión de áreas específicas

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
        $(`#${formId} input`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }


    function prevenirRepetidos(texto) {
        return texto.replace(/(.)\1{3,}/g, '$1$1$1');
    }

    // Función para verificar si el nombre ya existe en la tabla
    function existeEnTabla(texto, idIgnorar = null) {
        let existe = false;
        const textoNormalizado = texto.trim().toLowerCase();

        if (!textoNormalizado) return false;

        $('table tbody tr').each(function() {
            const btnEditar = $(this).find('.btn-editar');
            const idFila = btnEditar.data('id');

            if (idIgnorar && String(idFila) === String(idIgnorar)) {
                return;
            }

            const valorCelda = $(this).find('td:nth-child(2) span').text().trim().toLowerCase();
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
        limpiarErrores('formNuevoAreaEspecifica');

        const inputNombre = $('[name="nomAreaE"]');
        let nombre = inputNombre.val().trim();


        nombre = prevenirRepetidos(nombre);
        inputNombre.val(nombre);

        if (nombre === '') {
            mostrarError('nomAreaE', 'El nombre del área es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomAreaE', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nomAreaE', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (/(.)\1{3,}/.test(nombre)) {
            mostrarError('nomAreaE', 'No se permiten 4 o más caracteres iguales seguidos.');
            esValido = false;
        }

        return esValido;
    }

    // Validar formulario editar
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarAreaEspecifica');

        const id = $('#idAreaEEdit').val();
        const inputNombre = $('#nomAreaEEdit');
        let nombre = inputNombre.val().trim();


        nombre = prevenirRepetidos(nombre);
        inputNombre.val(nombre);

        if (nombre === '') {
            mostrarError('nomAreaEEdit', 'El nombre del área es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomAreaEEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 50) {
            mostrarError('nomAreaEEdit', 'El nombre no puede exceder los 50 caracteres.');
            esValido = false;
        } else if (/(.)\1{3,}/.test(nombre)) {
            mostrarError('nomAreaEEdit', 'No se permiten 4 o más caracteres iguales seguidos.');
            esValido = false;
        }

        return esValido;
    }

    // Limpiar errores al cerrar modales
    $('#modalAreaEspecifica').on('hidden.bs.modal', function() {
        $('#formNuevoAreaEspecifica')[0].reset();
        limpiarErrores('formNuevoAreaEspecifica');
    });

    $('#modalEditarAreaEspecifica').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarAreaEspecifica');
    });

    // Guardar
    $('#formNuevoAreaEspecifica').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        $.ajax({
            url: 'index.php?action=guardarAreaEspecifica',
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalAreaEspecifica').modal('hide');
                    $('#formNuevoAreaEspecifica')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="3"]').length > 0) {
                        $('table tbody').empty();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td class="fw-bold text-dark"><span>${response.nombre}</span></td>
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

    // Cargar datos en modal
    $(document).on('click', '.btn-editar', function() {
        const idArea = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarAreaEspecifica&id=' + idArea,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idAreaEEdit').val(data.idAreaE);
                    $('#nomAreaEEdit').val(data.nomAreaE);
                    limpiarErrores('formEditarAreaEspecifica');
                    $('#modalEditarAreaEspecifica').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del área.", "danger");
            }
        });
    });

    // Actualizar
    $('#formEditarAreaEspecifica').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idAreaEEdit').val();
        const nombreNuevo = $('#nomAreaEEdit').val();

        $.ajax({
            url: 'index.php?action=editarAreaEspecifica', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarAreaEspecifica').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    
                    const fila = $(`button[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nombreNuevo);
                    
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

    // Eliminar
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idArea = $(this).data('id');
        const fila = $(this).closest('tr');

        Validar.confirmar('¿Estás seguro de eliminar esta área específica?', function() {
            $.ajax({
                url: 'index.php?action=eliminarAreaEspecifica',
                type: 'POST',
                data: { idAreaE: idArea },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.css('transition', 'all 0.6s ease').addClass('fila-borrando').fadeOut(600, function() {
                            $(this).remove(); 
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="3" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay áreas específicas registradas actualmente.</td></tr>');
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