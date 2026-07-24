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

    function mostrarError(campo, mensaje) {
        const input = $(`[name="${campo}"]`);
        const errorDiv = $(`#error-${campo}`);
        
        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).show();
        }
    }

    function limpiarErrores(formId) {
        $(`#${formId} input, #${formId} select, #${formId} textarea`).removeClass('is-invalid vibrar-input');
        $(`#${formId} .invalid-feedback`).text('').hide();
    }

    // FUNCIÓN DE LIMPIEZA Y DISPARO DE ANIMACIÓN
    function aplicarLimpiezaYVibracion($el) {
        let valorActual = $el.val();
        // Detecta si existen 3 o más caracteres idénticos consecutivos
        if (/(.)\1{2,}/g.test(valorActual)) {
            // Activa animación de vibración CSS
            $el.addClass('vibrar-input');
            setTimeout(function() {
                $el.removeClass('vibrar-input');
            }, 300);

            // Reemplaza ráfagas repetidas reduciéndolas a 2
            let valorLimpio = valorActual;
            while (/(.)\1{2,}/g.test(valorLimpio)) {
                valorLimpio = valorLimpio.replace(/(.)\1{2,}/g, '$1$1');
            }
            $el.val(valorLimpio);
        }
    }

    // ESCUCHADOR EN TIEMPO REAL PARA AMBOS MODALES (INPUTS Y TEXTAREAS)
    $(document).on('input keyup paste', '#modalGrupoEtario input, #modalGrupoEtario textarea, #modalEditarGrupoEtario input, #modalEditarGrupoEtario textarea', function() {
        const $this = $(this);
        setTimeout(function() {
            aplicarLimpiezaYVibracion($this);
        }, 0);
    });

    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoGrupoEtario');

        const inputNombre = $('[name="nomGrupoEtareo"]');
        const inputDesc = $('[name="descGrupoEtareo"]');

        aplicarLimpiezaYVibracion(inputNombre);
        aplicarLimpiezaYVibracion(inputDesc);

        const nombre = inputNombre.val().trim();
        const edadMin = $('[name="edadMin"]').val();
        const edadMax = $('[name="edadMax"]').val();
        const descripcion = inputDesc.val().trim();

        if (nombre === '') {
            mostrarError('nomGrupoEtareo', 'El nombre del grupo es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomGrupoEtareo', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 25) {
            mostrarError('nomGrupoEtareo', 'El nombre no puede exceder los 25 caracteres.');
            esValido = false;
        } 

        if (edadMin === '' || edadMin === null) {
            mostrarError('edadMin', 'La edad mínima es obligatoria.');
            esValido = false;
        } else {
            const min = parseInt(edadMin, 10);
            if (isNaN(min) || min < 0) {
                mostrarError('edadMin', 'La edad mínima no puede ser negativa.');
                esValido = false;
            } else if (min > 120) {
                mostrarError('edadMin', 'La edad mínima no puede exceder 120 años.');
                esValido = false;
            }
        }

        if (edadMax === '' || edadMax === null) {
            mostrarError('edadMax', 'La edad máxima es obligatoria.');
            esValido = false;
        } else {
            const max = parseInt(edadMax, 10);
            if (isNaN(max) || max < 0) {
                mostrarError('edadMax', 'La edad máxima no puede ser negativa.');
                esValido = false;
            } else if (max > 120) {
                mostrarError('edadMax', 'La edad máxima no puede exceder 120 años.');
                esValido = false;
            }
        }

        if (edadMin !== '' && edadMax !== '') {
            const min = parseInt(edadMin, 10);
            const max = parseInt(edadMax, 10);
            if (!isNaN(min) && !isNaN(max) && min > max) {
                mostrarError('edadMax', 'La edad máxima debe ser mayor o igual a la edad mínima.');
                esValido = false;
            }
        }

        if (descripcion !== '' && descripcion.length > 250) {
            mostrarError('descGrupoEtareo', 'La descripción no puede exceder los 250 caracteres.');
            esValido = false;
        }

        return esValido;
    }

    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarGrupoEtario');

        const inputNombre = $('#nomGrupoEtareoEdit');
        const inputDesc = $('#descGrupoEtareoEdit');

        aplicarLimpiezaYVibracion(inputNombre);
        aplicarLimpiezaYVibracion(inputDesc);

        const nombre = inputNombre.val().trim();
        const edadMin = $('#edadMinEdit').val();
        const edadMax = $('#edadMaxEdit').val();
        const descripcion = inputDesc.val().trim();

        if (nombre === '') {
            mostrarError('nomGrupoEtareoEdit', 'El nombre del grupo es obligatorio.');
            esValido = false;
        } else if (nombre.length < 2) {
            mostrarError('nomGrupoEtareoEdit', 'El nombre debe tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombre.length > 25) {
            mostrarError('nomGrupoEtareoEdit', 'El nombre no puede exceder los 25 caracteres.');
            esValido = false;
        } 

        if (edadMin === '' || edadMin === null) {
            mostrarError('edadMinEdit', 'La edad mínima es obligatoria.');
            esValido = false;
        } else {
            const min = parseInt(edadMin, 10);
            if (isNaN(min) || min < 0) {
                mostrarError('edadMinEdit', 'La edad mínima no puede ser negativa.');
                esValido = false;
            } else if (min > 120) {
                mostrarError('edadMinEdit', 'La edad mínima no puede exceder 120 años.');
                esValido = false;
            }
        }

        if (edadMax === '' || edadMax === null) {
            mostrarError('edadMaxEdit', 'La edad máxima es obligatoria.');
            esValido = false;
        } else {
            const max = parseInt(edadMax, 10);
            if (isNaN(max) || max < 0) {
                mostrarError('edadMaxEdit', 'La edad máxima no puede ser negativa.');
                esValido = false;
            } else if (max > 120) {
                mostrarError('edadMaxEdit', 'La edad máxima no puede exceder 120 años.');
                esValido = false;
            }
        }

        if (edadMin !== '' && edadMax !== '') {
            const min = parseInt(edadMin, 10);
            const max = parseInt(edadMax, 10);
            if (!isNaN(min) && !isNaN(max) && min > max) {
                mostrarError('edadMaxEdit', 'La edad máxima debe ser mayor o igual a la edad mínima.');
                esValido = false;
            }
        }

        if (descripcion !== '' && descripcion.length > 250) {
            mostrarError('descGrupoEtareoEdit', 'La descripción no puede exceder los 250 caracteres.');
            esValido = false;
        }

        return esValido;
    }

    $('#modalGrupoEtario').on('hidden.bs.modal', function() {
        $('#formNuevoGrupoEtario')[0].reset();
        limpiarErrores('formNuevoGrupoEtario');
    });

    $('#modalEditarGrupoEtario').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarGrupoEtario');
    });

    // GUARDAR
    $('#formNuevoGrupoEtario').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        $.ajax({
            url: 'index.php?action=guardarGrupoEtario',
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalGrupoEtario').modal('hide');
                    $('#formNuevoGrupoEtario')[0].reset();
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="7"]').length > 0) {
                        $('table tbody').empty();
                    }

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td class="fw-bold text-dark"><span>${response.nombre}</span></td>
                            <td class="text-center"><span class="badge bg-info text-dark">${response.edadMin} años</span></td>
                            <td class="text-center"><span class="badge bg-info text-dark">${response.edadMax} años</span></td>
                            <td class="text-center"><span class="badge bg-primary">${response.edadMin} - ${response.edadMax} años</span></td>
                            <td class="text-muted small"><span>${response.descripcion || 'N/A'}</span></td>
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
        const idGrupo = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarGrupoEtario&id=' + idGrupo,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idGrupoEtareoEdit').val(data.idGrupoEtareo);
                    $('#nomGrupoEtareoEdit').val(data.nomGrupoEtareo);
                    $('#edadMinEdit').val(data.edadMin);
                    $('#edadMaxEdit').val(data.edadMax);
                    $('#descGrupoEtareoEdit').val(data.descGrupoEtareo);
                    limpiarErrores('formEditarGrupoEtario');
                    $('#modalEditarGrupoEtario').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del grupo.", "danger");
            }
        });
    });

    // ACTUALIZAR
    $('#formEditarGrupoEtario').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idGrupoEtareoEdit').val();
        const nuevoNombre = $('#nomGrupoEtareoEdit').val();
        const nuevaEdadMin = $('#edadMinEdit').val();
        const nuevaEdadMax = $('#edadMaxEdit').val();
        const nuevaDesc = $('#descGrupoEtareoEdit').val() || 'N/A';

        $.ajax({
            url: 'index.php?action=editarGrupoEtario', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarGrupoEtario').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    
                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2) span').text(nuevoNombre);
                    fila.find('td:nth-child(3) span').text(nuevaEdadMin + ' años');
                    fila.find('td:nth-child(4) span').text(nuevaEdadMax + ' años');
                    fila.find('td:nth-child(5) span').text(nuevaEdadMin + ' - ' + nuevaEdadMax + ' años');
                    fila.find('td:nth-child(6) span').text(nuevaDesc);
                    
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
        const idGrupo = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este grupo etario?')) {
            $.ajax({
                url: 'index.php?action=eliminarGrupoEtario',
                type: 'POST',
                data: { idGrupoEtareo: idGrupo },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.css('transition', 'all 0.6s ease').addClass('fila-borrando').fadeOut(600, function() {
                            $(this).remove(); 
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay grupos etarios registrados.</td></tr>');
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