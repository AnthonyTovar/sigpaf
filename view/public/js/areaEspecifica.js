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
        $(`#${formId} input`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    // FUNCIÓN DE LIMPIEZA: REDUCE CUALQUIER SECUENCIA DE 3+ CARACTERES IGUALES A MÁXIMO 2
    function prevenirRepetidos(texto) {
        let limpio = texto;
        while (/(.)\1{2,}/g.test(limpio)) {
            limpio = limpio.replace(/(.)\1{2,}/g, '$1$1');
        }
        return limpio;
    }

    // CONTROL EN TIEMPO REAL (INPUT, KEYUP, PASTE)
    $(document).on('input keyup paste', '#modalAreaEspecifica input, #modalEditarAreaEspecifica input', function() {
        const $this = $(this);
        setTimeout(function() {
            let valorActual = $this.val();
            let valorLimpio = prevenirRepetidos(valorActual);
            if (valorActual !== valorLimpio) {
                $this.val(valorLimpio);
            }
        }, 0);
    });

    // FUNCIÓN PARA VERIFICAR SI EL NOMBRE YA EXISTE EN LA TABLA
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

    // VALIDAR FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoAreaEspecifica');

        const inputNombre = $('[name="nomAreaE"]');
        let nombre = inputNombre.val().trim();

        // Limpieza de seguridad antes de procesar
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
        } else if (/(.)\1{2,}/.test(nombre)) {
            mostrarError('nomAreaE', 'No se permiten 3 o más caracteres iguales seguidos.');
            esValido = false;
        } else if (existeEnTabla(nombre)) {
            mostrarError('nomAreaE', 'Esta área específica ya se encuentra registrada.');
            esValido = false;
        }

        return esValido;
    }

    // VALIDAR FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarAreaEspecifica');

        const id = $('#idAreaEEdit').val();
        const inputNombre = $('#nomAreaEEdit');
        let nombre = inputNombre.val().trim();

        // Limpieza de seguridad antes de procesar
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
        } else if (/(.)\1{2,}/.test(nombre)) {
            mostrarError('nomAreaEEdit', 'No se permiten 3 o más caracteres iguales seguidos.');
            esValido = false;
        } else if (existeEnTabla(nombre, id)) {
            mostrarError('nomAreaEEdit', 'Esta área específica ya se encuentra registrada.');
            esValido = false;
        }

        return esValido;
    }

    // LIMPIAR ERRORES AL CERRAR MODALES
    $('#modalAreaEspecifica').on('hidden.bs.modal', function() {
        $('#formNuevoAreaEspecifica')[0].reset();
        limpiarErrores('formNuevoAreaEspecifica');
    });

    $('#modalEditarAreaEspecifica').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarAreaEspecifica');
    });

    // GUARDAR
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

    // CARGAR DATOS EN MODAL
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

    // ACTUALIZAR
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

    // ELIMINAR
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idArea = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar esta área específica?')) {
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
        }
    });
});