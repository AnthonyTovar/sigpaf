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

    // EXTRAER NÚMERO Y PREFIJO DE CÉDULA
    function extraerNumeroCedula(cedulaCompleta) {
        if (!cedulaCompleta) return '';
        return cedulaCompleta.replace(/^[VE]/i, '');
    }

    function extraerPrefijoCedula(cedulaCompleta) {
        if (!cedulaCompleta) return 'V';
        const match = cedulaCompleta.match(/^([VE])/i);
        return match ? match[1].toUpperCase() : 'V';
    }

    // VALIDAR NÚMERO DE CÉDULA (solo la parte numérica)
    function validarNumeroCedula(numero) {
        return /^\d{6,9}$/.test(numero.trim());
    }

    // VALIDAR TELÉFONO
    function validarTelefono(telefono) {
        if (!telefono) return true;
        return /^(0\d{3})-?\d{7}$/.test(telefono.replace(/\s/g, ''));
    }

    // VALIDAR CORREO ELECTRÓNICO
    function validarCorreo(correo) {
        if (!correo) return true;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
    }

    // VALIDAR FECHA DE NACIMIENTO
    function validarFechaNacimiento(fecha) {
        const hoy = new Date();
        const fechaNac = new Date(fecha);
        const edad = hoy.getFullYear() - fechaNac.getFullYear();
        const mes = hoy.getMonth() - fechaNac.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            return edad - 1;
        }
        return edad;
    }

    // ═══════════════════════════════════════════════
    // MÁSCARA AUTOMÁTICA DE TELÉFONO
    // ═══════════════════════════════════════════════
    $(document).on('input keydown', '#telefonoEmpleado, #telefonoEmpleadoEdit, input[name="telefonoEmpleado"], input[name="telefonoEmpleadoEdit"], .mask-telefono', function(e) {
        let input = $(this);
        let val = input.val();

        // Si el usuario presiona Backspace y el último carácter es el guion, lo elimina inmediatamente
        if (e.type === 'keydown' && e.key === 'Backspace') {
            if (val.endsWith('-')) {
                input.val(val.slice(0, -1));
                return;
            }
        }

        // Evento de entrada de texto (input)
        if (e.type === 'input') {
            let soloNumeros = val.replace(/\D/g, ''); // Deja únicamente dígitos
            const prefijosValidos = ['0416', '0426', '0412', '0422', '0414', '0424'];

            // Solo colocamos el guion si ya tiene MÁS de 4 dígitos
            if (soloNumeros.length > 4) {
                const prefijo = soloNumeros.substring(0, 4);
                if (prefijosValidos.includes(prefijo)) {
                    val = prefijo + '-' + soloNumeros.substring(4, 11);
                } else {
                    val = soloNumeros.substring(0, 11);
                }
            } else {
                // Si tiene 4 o menos dígitos, se mantiene limpio para permitir borrar fluido
                val = soloNumeros;
            }

            input.val(val);
        }
    });

    // ==========================================
    // VALIDACIONES DEL FORMULARIO NUEVO
    // ==========================================
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoEmpleado');

        const nacionalidad = $('#nacionalidad').val();
        const numeroCedula = $('#cedulaEmpleado').val().trim();
        const nombres = $('[name="nombres"]').val().trim();
        const apellidos = $('[name="apellidos"]').val().trim();
        const fechaNac = $('[name="fechaNacimiento"]').val();
        const telefono = $('[name="telefonoEmpleado"]').val().trim();
        const correo = $('[name="correoEmpleado"]').val().trim();
        const idCargo = $('[name="idCargo"]').val();
        const idUnidad = $('[name="idUnidadEjecutora"]').val();

        if (!nacionalidad) {
            mostrarError('nacionalidad', 'Debe seleccionar una nacionalidad.');
            esValido = false;
        }

        if (numeroCedula === '') {
            mostrarError('cedulaEmpleado', 'La cédula es obligatoria.');
            esValido = false;
        } else if (!validarNumeroCedula(numeroCedula)) {
            mostrarError('cedulaEmpleado', 'Debe contener entre 6 y 9 dígitos numéricos.');
            esValido = false;
        }

        if (nombres === '') {
            mostrarError('nombres', 'Los nombres son obligatorios.');
            esValido = false;
        } else if (nombres.length < 2) {
            mostrarError('nombres', 'Los nombres deben tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombres.length > 40) {
            mostrarError('nombres', 'Los nombres no pueden exceder los 40 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombres)) {
            mostrarError('nombres', 'Los nombres solo pueden contener letras y espacios.');
            esValido = false;
        }

        if (apellidos === '') {
            mostrarError('apellidos', 'Los apellidos son obligatorios.');
            esValido = false;
        } else if (apellidos.length < 2) {
            mostrarError('apellidos', 'Los apellidos deben tener al menos 2 caracteres.');
            esValido = false;
        } else if (apellidos.length > 40) {
            mostrarError('apellidos', 'Los apellidos no pueden exceder los 40 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(apellidos)) {
            mostrarError('apellidos', 'Los apellidos solo pueden contener letras y espacios.');
            esValido = false;
        }

        if (!fechaNac) {
            mostrarError('fechaNacimiento', 'La fecha de nacimiento es obligatoria.');
            esValido = false;
        } else {
            const edad = validarFechaNacimiento(fechaNac);
            if (edad < 18) {
                mostrarError('fechaNacimiento', 'El empleado debe ser mayor de edad (18 años).');
                esValido = false;
            } else if (edad > 100) {
                mostrarError('fechaNacimiento', 'La fecha de nacimiento no es válida.');
                esValido = false;
            }
        }

        if (telefono === '') {
            mostrarError('telefonoEmpleado', 'El teléfono no puede estar vacío.');
            esValido = false;
        } else if (telefono.length > 12) {
            mostrarError('telefonoEmpleado', 'El teléfono no puede exceder los 12 caracteres.');
            esValido = false;
        } else if (!/^\d{4}-\d{7}$/.test(telefono)) {
            mostrarError('telefonoEmpleado', 'Formato del telefono Invalido. Ejemplo: 0424-5555555');
            esValido = false;
        }

        if (correo === '') {
            mostrarError('correoEmpleado', 'El Correo no puede estar vacío.');
            esValido = false;
        } else if (correo.length > 150) {
            mostrarError('correoEmpleado', 'El correo no puede exceder los 150 caracteres.');
            esValido = false;
        } else if (!validarCorreo(correo)) {
            mostrarError('correoEmpleado', 'Ingrese un correo electrónico válido.');
            esValido = false;
        }

        if (!idCargo || idCargo === '') {
            mostrarError('idCargo', 'Debe seleccionar un cargo.');
            esValido = false;
        }

        if (!idUnidad || idUnidad === '') {
            mostrarError('idUnidadEjecutora', 'Debe seleccionar una unidad ejecutora.');
            esValido = false;
        }

        return esValido;
    }

    // ============================================
    // VALIDACIONES DEL FORMULARIO EDITAR
    // ============================================
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarEmpleado');

        const nacionalidad = $('#nacionalidadEdit').val();
        const numeroCedula = $('#cedulaEmpleadoEdit').val().trim();
        const nombres = $('#nombresEdit').val().trim();
        const apellidos = $('#apellidosEdit').val().trim();
        const fechaNac = $('#fechaNacimientoEdit').val();
        const telefono = $('#telefonoEmpleadoEdit').val().trim();
        const correo = $('#correoEmpleadoEdit').val().trim();
        const idCargo = $('#idCargoEdit').val();
        const idUnidad = $('#idUnidadEjecutoraEdit').val();

        if (!nacionalidad) {
            mostrarError('nacionalidadEdit', 'Debe seleccionar una nacionalidad.');
            esValido = false;
        }

        if (numeroCedula === '') {
            mostrarError('cedulaEmpleadoEdit', 'La cédula es obligatoria.');
            esValido = false;
        } else if (!validarNumeroCedula(numeroCedula)) {
            mostrarError('cedulaEmpleadoEdit', 'Debe contener entre 6 y 9 dígitos numéricos.');
            esValido = false;
        }

        if (nombres === '') {
            mostrarError('nombresEdit', 'Los nombres son obligatorios.');
            esValido = false;
        } else if (nombres.length < 2) {
            mostrarError('nombresEdit', 'Los nombres deben tener al menos 2 caracteres.');
            esValido = false;
        } else if (nombres.length > 40) {
            mostrarError('nombresEdit', 'Los nombres no pueden exceder los 40 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombres)) {
            mostrarError('nombresEdit', 'Los nombres solo pueden contener letras y espacios.');
            esValido = false;
        }

        if (apellidos === '') {
            mostrarError('apellidosEdit', 'Los apellidos son obligatorios.');
            esValido = false;
        } else if (apellidos.length < 2) {
            mostrarError('apellidosEdit', 'Los apellidos deben tener al menos 2 caracteres.');
            esValido = false;
        } else if (apellidos.length > 40) {
            mostrarError('apellidosEdit', 'Los apellidos no pueden exceder los 40 caracteres.');
            esValido = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(apellidos)) {
            mostrarError('apellidosEdit', 'Los apellidos solo pueden contener letras y espacios.');
            esValido = false;
        }

        if (!fechaNac) {
            mostrarError('fechaNacimientoEdit', 'La fecha de nacimiento es obligatoria.');
            esValido = false;
        } else {
            const edad = validarFechaNacimiento(fechaNac);
            if (edad < 18) {
                mostrarError('fechaNacimientoEdit', 'El empleado debe ser mayor de edad (18 años).');
                esValido = false;
            } else if (edad > 100) {
                mostrarError('fechaNacimientoEdit', 'La fecha de nacimiento no es válida.');
                esValido = false;
            }
        }

        if (telefono === '') {
            mostrarError('telefonoEmpleadoEdit', 'El teléfono no puede estar vacío.');
            esValido = false;
        } else if (telefono.length > 12) {
            mostrarError('telefonoEmpleadoEdit', 'El teléfono no puede exceder los 12 caracteres.');
            esValido = false;
        } else if (!/^\d{4}-\d{7}$/.test(telefono)) {
            mostrarError('telefonoEmpleadoEdit', 'Formato del telefono Invalido. Ejemplo: 0424-5555555');
            esValido = false;
        }

        if (correo === '') {
            mostrarError('correoEmpleadoEdit', 'El Correo no puede estar vacío.');
            esValido = false;
        } else if (correo.length > 150) {
            mostrarError('correoEmpleadoEdit', 'El correo no puede exceder los 150 caracteres.');
            esValido = false;
        } else if (!validarCorreo(correo)) {
            mostrarError('correoEmpleadoEdit', 'Ingrese un correo electrónico válido.');
            esValido = false;
        }

        if (!idCargo || idCargo === '') {
            mostrarError('idCargoEdit', 'Debe seleccionar un cargo.');
            esValido = false;
        }

        if (!idUnidad || idUnidad === '') {
            mostrarError('idUnidadEjecutoraEdit', 'Debe seleccionar una unidad ejecutora.');
            esValido = false;
        }

        return esValido;
    }

    // LIMPIAR ERRORES AL CERRAR MODALES
    $('#modalEmpleado').on('hidden.bs.modal', function() {
        $('#formNuevoEmpleado')[0].reset();
        $('#nacionalidad').val('V');
        limpiarErrores('formNuevoEmpleado');
    });

    $('#modalEditarEmpleado').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarEmpleado');
    });

    // ============================================
    // Construye datos correctamente y maneja cédula duplicada
    // ============================================
    $('#formNuevoEmpleado').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const nombreCargo = $('select[name="idCargo"] option:selected').text();
        const nombreUnidad = $('select[name="idUnidadEjecutora"] option:selected').text();

        // Construir cédula completa: prefijo + número
        const nacionalidad = $('#nacionalidad').val();
        const numeroCedula = $('#cedulaEmpleado').val().trim();
        const cedulaCompleta = nacionalidad + numeroCedula;

        // Construir datos manualmente
        const formData = {
            nacionalidad: nacionalidad,
            cedulaEmpleado: cedulaCompleta,
            nombres: $('[name="nombres"]').val().trim(),
            apellidos: $('[name="apellidos"]').val().trim(),
            fechaNacimiento: $('[name="fechaNacimiento"]').val(),
            telefonoEmpleado: $('[name="telefonoEmpleado"]').val().trim(),
            correoEmpleado: $('[name="correoEmpleado"]').val().trim(),
            idCargo: $('[name="idCargo"]').val(),
            idUnidadEjecutora: $('[name="idUnidadEjecutora"]').val()
        };

        $.ajax({
            url: 'index.php?action=guardarEmpleado',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEmpleado').modal('hide');
                    $('#formNuevoEmpleado')[0].reset();
                    $('#nacionalidad').val('V');
                    lanzarAviso(response.message, 'success');

                    if ($('table tbody tr td[colspan="10"]').length > 0) {
                        $('table tbody tr td[colspan="10"]').closest('tr').remove();
                    }

                    const fechaFormateada = new Date(response.fechaNac).toLocaleDateString('es-VE');
                    const telefono = response.telefono || 'N/A';
                    const correo = response.correo ? '<i class="bi bi-envelope-fill me-1 text-primary"></i>' + response.correo : 'N/A';

                    const nuevaFila = `
                        <tr style="display:none;">
                            <td><span class="badge bg-secondary px-2 py-1">${response.id}</span></td>
                            <td>${response.cedula}</td>
                            <td><span class="fw-bold text-dark">${response.nombres}</span></td>
                            <td><span class="fw-bold text-dark">${response.apellidos}</span></td>
                            <td><span class="text-muted small">${fechaFormateada}</span></td>
                            <td><span class="text-muted small">${telefono}</span></td>
                            <td><span class="text-muted small">${correo}</span></td>
                            <td><span class="text-muted small">${nombreCargo}</span></td>
                            <td><span class="text-muted small">${nombreUnidad}</span></td>
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

    // CARGAR DATOS EN MODAL DE EDICIÓN
    $(document).on('click', '.btn-editar', function() {
        const idEmpleado = $(this).data('id');
        $.ajax({
            url: 'index.php?action=consultarEmpleado&id=' + idEmpleado,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#idEmpleadoEdit').val(data.idEmpleado);
                    const prefijo = extraerPrefijoCedula(data.cedulaEmpleado);
                    const numero = extraerNumeroCedula(data.cedulaEmpleado);
                    $('#nacionalidadEdit').val(prefijo);
                    $('#cedulaEmpleadoEdit').val(numero);
                    $('#nombresEdit').val(data.nombres);
                    $('#apellidosEdit').val(data.apellidos);
                    $('#fechaNacimientoEdit').val(data.fechaNacimiento);
                    $('#telefonoEmpleadoEdit').val(data.telefonoEmpleado);
                    $('#correoEmpleadoEdit').val(data.correoEmpleado);
                    $('#idCargoEdit').val(data.idCargo);
                    $('#idUnidadEjecutoraEdit').val(data.idUnidadEjecutora);
                    limpiarErrores('formEditarEmpleado');
                    $('#modalEditarEmpleado').modal('show');
                }
            },
            error: function() {
                lanzarAviso("No se pudieron cargar los datos del empleado.", "danger");
            }
        });
    });

    // ============================================
    // ACTUALIZAR EMPLEADO
    // ============================================
    $('#formEditarEmpleado').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idEmpleadoEdit').val();
        const nacionalidad = $('#nacionalidadEdit').val();
        const numeroCedula = $('#cedulaEmpleadoEdit').val().trim();
        const cedulaCompleta = nacionalidad + numeroCedula;
        const nuevoNombre = $('#nombresEdit').val();
        const nuevoApellido = $('#apellidosEdit').val();
        const nuevaFecha = $('#fechaNacimientoEdit').val();
        const nuevoTelefono = $('#telefonoEmpleadoEdit').val() || 'N/A';
        const nuevoCorreo = $('#correoEmpleadoEdit').val();
        const nuevoCargo = $('#idCargoEdit option:selected').text();
        const nuevaUnidad = $('#idUnidadEjecutoraEdit option:selected').text();

        const formData = {
            idEmpleadoEdit: idActualizado,
            nacionalidadEdit: nacionalidad,
            cedulaEmpleadoEdit: cedulaCompleta,
            nombresEdit: nuevoNombre,
            apellidosEdit: nuevoApellido,
            fechaNacimientoEdit: nuevaFecha,
            telefonoEmpleadoEdit: $('#telefonoEmpleadoEdit').val().trim(),
            correoEmpleadoEdit: nuevoCorreo,
            idCargoEdit: $('#idCargoEdit').val(),
            idUnidadEjecutoraEdit: $('#idUnidadEjecutoraEdit').val()
        };

        $.ajax({
            url: 'index.php?action=editarEmpleado',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarEmpleado').modal('hide');
                    lanzarAviso(response.message, 'warning');

                    const fechaFormateada = new Date(nuevaFecha).toLocaleDateString('es-VE');
                    const correoHtml = nuevoCorreo ? '<i class="bi bi-envelope-fill me-1 text-primary"></i>' + nuevoCorreo : 'N/A';

                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2)').text(cedulaCompleta);
                    fila.find('td:nth-child(3) span').text(nuevoNombre);
                    fila.find('td:nth-child(4) span').text(nuevoApellido);
                    fila.find('td:nth-child(5) span').text(fechaFormateada);
                    fila.find('td:nth-child(6) span').text(nuevoTelefono);
                    fila.find('td:nth-child(7) span').html(correoHtml);
                    fila.find('td:nth-child(8) span').text(nuevoCargo);
                    fila.find('td:nth-child(9) span').text(nuevaUnidad);
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

    // ELIMINAR EMPLEADO
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        const idEmpleado = $(this).data('id');
        const fila = $(this).closest('tr');

        if (confirm('¿Estás seguro de eliminar este empleado?')) {
            $.ajax({
                url: 'index.php?action=eliminarEmpleado',
                type: 'POST',
                data: { idEmpleado: idEmpleado },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        lanzarAviso(response.message, "danger");

                        fila.fadeOut(600, function() {
                            $(this).remove();

                            if ($('table tbody tr').length === 0) {
                                $('table tbody').append('<tr><td colspan="10" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay empleados registrados actualmente.</td></tr>');
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