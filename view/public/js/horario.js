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
        alerta.stop(true, true).fadeIn(400).delay(3500).fadeOut(400);
    }

    // ═══════════════════════════════════════════════
    // MOSTRAR / LIMPIAR ERRORES
    // ═══════════════════════════════════════════════
    function mostrarError(campo, mensaje) {
        var input = $('[name="' + campo + '"]');
        var errorDiv = $('#error-' + campo);
        input.addClass('is-invalid');
        if (errorDiv.length) {
            errorDiv.text(mensaje).css('display', 'block');
        }
    }

    function limpiarErrores(formId) {
        $('#' + formId + ' input').removeClass('is-invalid');
        $('#' + formId + ' .invalid-feedback').text('').css('display', 'none');
    }

    // EXPRESIÓN REGULAR PARA FORMATO: 08:00 AM - 12:00 PM
    const regexHorario = /^(0[1-9]|1[0-2]):[0-5][0-9]\s(AM|PM)\s-\s(0[1-9]|1[0-2]):[0-5][0-9]\s(AM|PM)$/;

    function validarFormNuevo() {
        limpiarErrores('formNuevoHorario');
        let esValido = true;
        const nombre = $('input[name="nomHorario"]').val().trim();

        if (nombre === '') {
            mostrarError('nomHorario', 'El rango de horario es obligatorio.');
            esValido = false;
        } else if (!regexHorario.test(nombre)) {
            mostrarError('nomHorario', 'Formato inválido. Use: 08:00 AM - 12:00 PM');
            esValido = false;
        }
        return esValido;
    }

    function validarFormEditar() {
        limpiarErrores('formEditarHorario');
        let esValido = true;
        const nombre = $('#nomHorarioEdit').val().trim();

        if (nombre === '') {
            mostrarError('nomHorarioEdit', 'El rango de horario es obligatorio.');
            esValido = false;
        } else if (!regexHorario.test(nombre)) {
            mostrarError('nomHorarioEdit', 'Formato inválido. Use: 08:00 AM - 12:00 PM');
            esValido = false;
        }
        return esValido;
    }

    // ═══════════════════════════════════════════════
    // EVENTOS Y AJAX
    // ═══════════════════════════════════════════════

    $('#modalHorario').on('hidden.bs.modal', function() {
        $('#formNuevoHorario')[0].reset();
        limpiarErrores('formNuevoHorario');
    });

    $('#modalEditarHorario').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarHorario');
    });

    //GUARDAR NUEVO HORARIO
    $('#formNuevoHorario').on('submit', function(e) {
        e.preventDefault(); 
        if (!validarFormNuevo()) return false;

        $.ajax({
            url: 'index.php?action=guardarHorario',
            type: 'POST', 
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalHorario').modal('hide');
                    $('#formNuevoHorario')[0].reset();
                    lanzarAviso(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            }
        });
    });

    //CARGAR PARA EDITAR
    $(document).on('click', '.btn-editar', function() {
        const idHorario = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarHorario&id=' + idHorario,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idHorarioEdit').val(data.idHorario);
                    $('#nomHorarioEdit').val(data.nomHorario);
                    limpiarErrores('formEditarHorario');
                    $('#modalEditarHorario').modal('show');
                }
            }
        });
    });

    //ACTUALIZAR HORARIO
    $('#formEditarHorario').on('submit', function(e) {
        e.preventDefault();
        if (!validarFormEditar()) return false;

        const idActualizado = $('#idHorarioEdit').val();
        const nombreNuevo = $('#nomHorarioEdit').val();

        $.ajax({
            url: 'index.php?action=editarHorario',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarHorario').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    
                    const fila = $('button[data-id="' + idActualizado + '"]').closest('tr');
                    fila.find('td:nth-child(2) span').text(nombreNuevo);
                    fila.fadeOut(100).fadeIn(800);
                } else {
                    lanzarAviso(response.message, 'danger');
                }
            }
        });
    });

    //ELIMINAR HORARIO
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault(); 
        const idHorario = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este horario?')) {
            $.ajax({
                url: 'index.php?action=eliminarHorario',
                type: 'POST',
                data: { idHorario: idHorario },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");
                        fila.fadeOut(600, function() {
                            $(this).remove();
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="3" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay horarios registrados actualmente.</td></tr>');
                            }
                        });
                    } else {
                        lanzarAviso(response.message, "danger");
                    }
                }
            });
        }
    });

});