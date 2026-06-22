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

    // VALIDAR CÉDULA VENEZOLANA
    function validarCedula(cedula) {
        return /^[VEve]\d{6,9}$/.test(cedula.replace(/-/g, ''));
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

    // VALIDACIONES DEL FORMULARIO NUEVO
    function validarFormNuevo() {
        let esValido = true;
        limpiarErrores('formNuevoEmpleado');

        const cedula = $('[name="cedulaEmpleado"]').val().trim();
        const nombres = $('[name="nombres"]').val().trim();
        const apellidos = $('[name="apellidos"]').val().trim();
        const fechaNac = $('[name="fechaNacimiento"]').val();
        const telefono = $('[name="telefonoEmpleado"]').val().trim();
        const correo = $('[name="correoEmpleado"]').val().trim();
        const idCargo = $('[name="idCargo"]').val();
        const idUnidad = $('[name="idUnidadEjecutora"]').val();

        if (cedula === '') {
            mostrarError('cedulaEmpleado', 'La cédula es obligatoria.');
            esValido = false;
        } else if (!validarCedula(cedula)) {
            mostrarError('cedulaEmpleado', 'Formato inválido. Use V12345678 o E12345678.');
            esValido = false;
        } else if (cedula.replace(/-/g, '').length > 10) {
            mostrarError('cedulaEmpleado', 'La cédula no puede exceder 9 dígitos más la letra.');
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

    // VALIDACIONES DEL FORMULARIO EDITAR
    function validarFormEditar() {
        let esValido = true;
        limpiarErrores('formEditarEmpleado');

        const cedula = $('#cedulaEmpleadoEdit').val().trim();
        const nombres = $('#nombresEdit').val().trim();
        const apellidos = $('#apellidosEdit').val().trim();
        const fechaNac = $('#fechaNacimientoEdit').val();
        const telefono = $('#telefonoEmpleadoEdit').val().trim();
        const correo = $('#correoEmpleadoEdit').val().trim();
        const idCargo = $('#idCargoEdit').val();
        const idUnidad = $('#idUnidadEjecutoraEdit').val();

        if (cedula === '') {
            mostrarError('cedulaEmpleadoEdit', 'La cédula es obligatoria.');
            esValido = false;
        } else if (!validarCedula(cedula)) {
            mostrarError('cedulaEmpleadoEdit', 'Formato inválido. Use V12345678 o E12345678.');
            esValido = false;
        } else if (cedula.replace(/-/g, '').length > 10) {
            mostrarError('cedulaEmpleadoEdit', 'La cédula no puede exceder 9 dígitos más la letra.');
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
        limpiarErrores('formNuevoEmpleado');
    });

    $('#modalEditarEmpleado').on('hidden.bs.modal', function() {
        limpiarErrores('formEditarEmpleado');
    });

    // GUARDAR NUEVO EMPLEADO
    $('#formNuevoEmpleado').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormNuevo()) {
            return;
        }

        const nombreCargo = $('select[name="idCargo"] option:selected').text();
        const nombreUnidad = $('select[name="idUnidadEjecutora"] option:selected').text();

        $.ajax({
            url: 'index.php?action=guardarEmpleado',
            type: 'POST', 
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEmpleado').modal('hide');
                    $('#formNuevoEmpleado')[0].reset();
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
            error: function() {
                lanzarAviso("Error al procesar el registro", "danger");
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
                    $('#cedulaEmpleadoEdit').val(data.cedulaEmpleado);
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

    // ACTUALIZAR EMPLEADO
    $('#formEditarEmpleado').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormEditar()) {
            return;
        }

        const idActualizado = $('#idEmpleadoEdit').val();
        const nuevoNombre = $('#nombresEdit').val();
        const nuevoApellido = $('#apellidosEdit').val();
        const nuevaCedula = $('#cedulaEmpleadoEdit').val();
        const nuevaFecha = $('#fechaNacimientoEdit').val();
        const nuevoTelefono = $('#telefonoEmpleadoEdit').val() || 'N/A';
        const nuevoCorreo = $('#correoEmpleadoEdit').val();
        const nuevoCargo = $('#idCargoEdit option:selected').text();
        const nuevaUnidad = $('#idUnidadEjecutoraEdit option:selected').text();

        $.ajax({
            url: 'index.php?action=editarEmpleado', 
            type: 'POST',
            data: $(this).serialize(), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalEditarEmpleado').modal('hide');
                    lanzarAviso(response.message, 'warning');
                    
                    const fechaFormateada = new Date(nuevaFecha).toLocaleDateString('es-VE');
                    const correoHtml = nuevoCorreo ? '<i class="bi bi-envelope-fill me-1 text-primary"></i>' + nuevoCorreo : 'N/A';
                    
                    const fila = $(`.btn-editar[data-id="${idActualizado}"]`).closest('tr');
                    fila.find('td:nth-child(2)').text(nuevaCedula);
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
            error: function() {
                lanzarAviso("Error al actualizar el registro", "danger");
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