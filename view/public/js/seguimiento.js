// Gestión de seguimiento

$(document).ready(function () {

    let sesionesCache = [];
    let asistentesCache = {};
    let beneficiariosCache = [];
    let hoy = '';


    let idActividadActual = '';
    let estatusOriginal = '';
    let puedeEditarEstatus = false;
    let estatusPendiente = '';

    function lanzarAviso(mensaje, tipo) {
        Validar.aviso(mensaje, tipo);
    }

    function mostrarError(campo, mensaje) {
        const input = $(`[name="${campo}"]`).first();
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

    function formatearFecha(fechaStr) {
        if (!fechaStr) return '-';
        const f = new Date(fechaStr + 'T00:00:00');
        return f.toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' });
    }


    // Abrir modal de seguimimiento

    $(document).on('click', '.btn-seguimiento', function () {
        const idActividad = $(this).data('id');
        const nombre = $(this).data('nombre');


        idActividadActual = idActividad;
        estatusOriginal = $(this).data('estatus') || '';
        puedeEditarEstatus = window.SeguimientoCfg.esAdmin ||
            ($(this).data('empleado') || '') === window.SeguimientoCfg.miEmpleado;
        configurarCampoEstatus();

        $('#seg-nombre-actividad').text(nombre);
        limpiarErrores('formSeguimiento');
        $('#formSeguimiento')[0].reset();
        $('#idSegActividad').val('');
        $('#fechaSesionTexto').val('-');
        $('#lista-beneficiarios').html(
            '<p class="text-muted small mb-0 px-2 py-3 text-center"><span class="spinner-border spinner-border-sm me-1"></span>Cargando...</p>'
        );
        $('#selectSesion').html('<option value="">Cargando sesiones...</option>');
        $('#contadorAsistentes').text('0');

        $.ajax({
            url: 'index.php?action=obtenerSesionesSeguimiento&idActividad=' + encodeURIComponent(idActividad),
            type: 'GET',
            dataType: 'json',
            success: function (resp) {
                if (resp.status !== 'success') {
                    lanzarAviso(resp.message || 'No se pudieron cargar las sesiones.', 'danger');
                    return;
                }
                sesionesCache = resp.sesiones || [];
                beneficiariosCache = resp.beneficiarios || [];
                asistentesCache = resp.asistentes || {};
                hoy = resp.hoy || '';

                pintarSesiones();
                pintarBeneficiarios([]);

                $('#modalSeguimiento').modal('show');
            },
            error: function () {
                lanzarAviso('No se pudieron cargar los datos de seguimiento.', 'danger');
            }
        });
    });


    function pintarSesiones() {
        const select = $('#selectSesion');
        select.empty();

        if (sesionesCache.length === 0) {
            select.append('<option value="">Esta actividad no tiene sesiones</option>');
            return;
        }

        select.append('<option value="">Seleccione una sesión...</option>');

        sesionesCache.forEach(function (s) {
            const registrada = (s.logroActividad || '') !== '';
            const futura = s.fechaSesion > hoy;
            let texto = 'Sesión ' + s.nroSesionPlanificada + ' — ' + formatearFecha(s.fechaSesion);
            if (registrada) texto += ' (registrada ✓)';
            if (futura) texto += ' (futura)';

            const opcion = $('<option>', {
                value: s.idSegActividad,
                text: texto,
                disabled: futura
            });
            select.append(opcion);
        });
    }


    function pintarBeneficiarios(marcados) {
        const cont = $('#lista-beneficiarios');

        if (beneficiariosCache.length === 0) {
            cont.html('<p class="text-muted small mb-0 px-2 py-3 text-center"><i class="bi bi-inbox me-1"></i>No hay beneficiarios registrados. Agregue uno con el botón verde.</p>');
            $('#contadorAsistentes').text('0');
            return;
        }

        let html = '';
        beneficiariosCache.forEach(function (b) {
            const marcado = marcados.indexOf(b.idBeneficiario) !== -1;
            html +=
                '<div class="form-check beneficiario-item">' +
                    '<input class="form-check-input asistencia-check" type="checkbox" ' +
                        'value="' + b.idBeneficiario + '" id="ben-' + b.idBeneficiario + '" ' + (marcado ? 'checked' : '') + '>' +
                    '<label class="form-check-label small" for="ben-' + b.idBeneficiario + '">' +
                        '<strong>' + b.apellidosBeneficiario + ', ' + b.nombresBeneficiario + '</strong>' +
                        ' <span class="text-muted">(' + b.cedulaBeneficiario + ')</span>' +
                    '</label>' +
                '</div>';
        });
        cont.html(html);
        actualizarContador();
    }

    function actualizarContador() {
        const total = $('.asistencia-check:checked').length;
        $('#contadorAsistentes').text(total);
    }

    $(document).on('change', '.asistencia-check', actualizarContador);


    $(document).on('change', '#selectSesion', function () {
        limpiarErrores('formSeguimiento');
        const idSeg = $(this).val();

        const sesion = sesionesCache.find(function (s) {
            return s.idSegActividad === idSeg;
        });

        if (!sesion) {
            $('#idSegActividad').val('');
            $('#fechaSesionTexto').val('-');
            $('#logroActividad, #observObstaculo').val('');
            $('#idTipEntrega').val('');
            pintarBeneficiarios([]);
            return;
        }

        $('#idSegActividad').val(sesion.idSegActividad);
        $('#fechaSesionTexto').val(formatearFecha(sesion.fechaSesion) +
            (sesion.nomTipEntrega ? ' · ' + sesion.nomTipEntrega : ''));
        $('#logroActividad').val(sesion.logroActividad || '');
        $('#observObstaculo').val(sesion.observObstaculo || '');
        $('#idTipEntrega').val(sesion.idTipEntrega || '');

        pintarBeneficiarios(asistentesCache[sesion.idSegActividad] || []);
    });


    // Guardar seguimiento

    $('#formSeguimiento').on('submit', function (e) {
        e.preventDefault();
        limpiarErrores('formSeguimiento');

        const idSeg = $('#idSegActividad').val().trim();
        const logro = $('#logroActividad').val().trim();
        const obstaculo = $('#observObstaculo').val().trim();
        const idTipEntrega = $('#idTipEntrega').val();

        let valido = true;

        if (!idSeg) {
            $('#selectSesion').addClass('is-invalid');
            $('#error-idSegActividad').text('Debe seleccionar una sesión.').show();
            valido = false;
        }
        if (logro === '') {
            mostrarError('logroActividad', 'Debe describir el logro de la sesión.');
            valido = false;
        } else if (logro.length < 10) {
            mostrarError('logroActividad', 'El logro debe tener al menos 10 caracteres.');
            valido = false;
        }
        if (!idTipEntrega) {
            mostrarError('idTipEntrega', 'Debe seleccionar el tipo de entrega.');
            valido = false;
        }

        if (!valido) return;

        const asistentes = $('.asistencia-check:checked').map(function () {
            return $(this).val();
        }).get();

        const btnGuardar = $('#formSeguimiento button[type="submit"]');
        btnGuardar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');

        $.ajax({
            url: 'index.php?action=guardarSeguimiento',
            type: 'POST',
            data: {
                idSegActividad: idSeg,
                logroActividad: logro,
                observObstaculo: obstaculo,
                idTipEntrega: idTipEntrega,
                asistentes: asistentes
            },
            dataType: 'json',
            success: function (resp) {
                if (resp.status === 'success') {
                    $('#modalSeguimiento').modal('hide');
                    lanzarAviso(resp.message, 'success');
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    lanzarAviso(resp.message, 'danger');
                }
            },
            error: function () {
                lanzarAviso('Error al registrar el seguimiento.', 'danger');
            },
            complete: function () {
                btnGuardar.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Guardar Seguimiento');
            }
        });
    });


    // Nuevo beneficiario rápido

    $('#formNuevoBeneficiario').on('submit', function (e) {
        e.preventDefault();
        limpiarErrores('formNuevoBeneficiario');

        const cedula = $('[name="cedulaBeneficiario"]').val().trim();
        const nombres = $('[name="nombresBeneficiario"]').val().trim();
        const apellidos = $('[name="apellidosBeneficiario"]').val().trim();
        const nacionalidad = $('[name="nacionalidad"]').val();
        const fechaNac = $('[name="fechaNacBeneficiario"]').val();
        const sexo = $('[name="sexoBeneficiario"]').val();

        let valido = true;

        if (cedula === '') {
            mostrarError('cedulaBeneficiario', 'La cédula es obligatoria.');
            valido = false;
        } else if (!/^[VE]?\d{6,9}$/i.test(cedula)) {
            mostrarError('cedulaBeneficiario', 'Formato de cédula inválido (V12345678).');
            valido = false;
        }
        if (nombres === '') {
            mostrarError('nombresBeneficiario', 'Los nombres son obligatorios.');
            valido = false;
        }
        if (apellidos === '') {
            mostrarError('apellidosBeneficiario', 'Los apellidos son obligatorios.');
            valido = false;
        }
        if (!nacionalidad) {
            mostrarError('nacionalidad', 'Seleccione la nacionalidad.');
            valido = false;
        }
        if (!sexo) {
            mostrarError('sexoBeneficiario', 'Seleccione el sexo.');
            valido = false;
        }

        if (!valido) return;

        const btn = $('#formNuevoBeneficiario button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');

        $.ajax({
            url: 'index.php?action=guardarBeneficiarioSeguimiento',
            type: 'POST',
            data: {
                cedulaBeneficiario: cedula,
                nombresBeneficiario: nombres,
                apellidosBeneficiario: apellidos,
                nacionalidad: nacionalidad,
                fechaNacBeneficiario: fechaNac,
                sexoBeneficiario: sexo
            },
            dataType: 'json',
            success: function (resp) {
                if (resp.status === 'success') {

                    beneficiariosCache.push({
                        idBeneficiario: resp.id,
                        cedulaBeneficiario: resp.cedula,
                        nombresBeneficiario: resp.nombres,
                        apellidosBeneficiario: resp.apellidos
                    });
                    const marcados = $('.asistencia-check:checked').map(function () {
                        return $(this).val();
                    }).get();
                    marcados.push(resp.id);
                    pintarBeneficiarios(marcados);

                    $('#modalNuevoBeneficiario').modal('hide');
                    $('#formNuevoBeneficiario')[0].reset();
                    lanzarAviso(resp.message, 'success');
                } else {
                    lanzarAviso(resp.message, 'danger');
                }
            },
            error: function () {
                lanzarAviso('Error al registrar el beneficiario.', 'danger');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Guardar Beneficiario');
            }
        });
    });


    $('#modalNuevoBeneficiario').on('hidden.bs.modal', function () {
        $('body').addClass('modal-open');
    });




    function nombreDeEstatus(idEstatus) {
        const encontrado = (window.SeguimientoCfg.estatus || []).find(function (e) {
            return e.idEstatus === idEstatus;
        });
        return encontrado ? encontrado.nomEstatus : idEstatus;
    }


    function claseBadgeEstatus(nombreEstatus) {
        const est = (nombreEstatus || '').toLowerCase();
        if (est.indexOf('final') !== -1) return 'bg-success';
        if (est.indexOf('cancel') !== -1 || est.indexOf('espera') !== -1) return 'bg-danger';
        return 'bg-warning text-dark';
    }



    function actualizarBadgeTabla(idActividad, idEstatus) {
        const fila = $('.btn-seguimiento[data-id="' + idActividad + '"]').closest('tr');
        const badge = fila.find('.badge-estatus');
        if (!badge.length) return;
        const nombre = nombreDeEstatus(idEstatus);
        badge.text(nombre);
        badge.attr('class', 'badge badge-estatus ' + claseBadgeEstatus(nombre));
    }



    function configurarCampoEstatus() {
        const campo = $('#campoEstatusActividad');
        const select = $('#selectEstatusActividad');

        if (!puedeEditarEstatus) {
            campo.hide();
            select.empty();
            return;
        }

        select.empty();
        (window.SeguimientoCfg.estatus || []).forEach(function (e) {
            select.append($('<option>', {
                value: e.idEstatus,
                text: e.nomEstatus,
                selected: e.idEstatus === estatusOriginal
            }));
        });
        campo.show();
    }


    $(document).on('change', '#selectEstatusActividad', function () {
        const nuevo = $(this).val();


        if (nuevo === estatusOriginal) {
            return;
        }

        estatusPendiente = nuevo;
        $('#textoConfirmarEstatus').html(
            'El estatus cambiará de <span class="badge bg-secondary">' +
            $('<span>').text(nombreDeEstatus(estatusOriginal)).html() +
            '</span> a <span class="badge bg-warning text-dark">' +
            $('<span>').text(nombreDeEstatus(nuevo)).html() +
            '</span>.'
        );

        const modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirmarEstatus'));
        modalConfirm.show();
    });


    $('#modalConfirmarEstatus').on('hidden.bs.modal', function () {
        if (estatusPendiente) {
            $('#selectEstatusActividad').val(estatusOriginal);
            estatusPendiente = '';
        }
    });


    $('#btnConfirmarEstatus').on('click', function () {
        if (!estatusPendiente) {
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEstatus')).hide();
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Cambiando...');

        $.ajax({
            url: 'index.php?action=cambiarEstatusActividad',
            type: 'POST',
            data: { idActividad: idActividadActual, idEstatus: estatusPendiente },
            dataType: 'json',
            success: function (resp) {
                bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEstatus')).hide();
                if (resp.status === 'success') {
                    estatusOriginal = estatusPendiente;
                    $('#selectEstatusActividad').val(estatusOriginal);
                    $('.btn-seguimiento[data-id="' + idActividadActual + '"]').data('estatus', estatusOriginal);

                    actualizarBadgeTabla(idActividadActual, estatusOriginal);
                    lanzarAviso(resp.message, 'success');
                } else {
                    lanzarAviso(resp.message || 'No se pudo cambiar el estatus.', 'danger');
                    $('#selectEstatusActividad').val(estatusOriginal);
                }
            },
            error: function () {
                bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEstatus')).hide();
                lanzarAviso('Error al cambiar el estatus.', 'danger');
                $('#selectEstatusActividad').val(estatusOriginal);
            },
            complete: function () {
                estatusPendiente = '';
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Sí, cambiar estatus');
            }
        });
    });
});
