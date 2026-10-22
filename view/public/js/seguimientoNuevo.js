$(document).ready(function () {

    const cfg = window.SeguimientoNuevoCfg || {};
    const idActividadActual = cfg.idActividad || '';
    let estatusOriginal = cfg.estatusOriginal || '';

    let sesionesCache = [];
    let asistentesCache = {};
    let beneficiariosCache = [];
    let hoy = '';

    // Estado del cambio de estatus
    let estatusPendiente = '';

    // Navegación del asistente (3 pasos)
    const TOTAL_PASOS = 3;
    let pasoActual = 1;

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

    // ============================================================
    // NAVEGACIÓN DEL ASISTENTE
    // ============================================================
    function mostrarPaso(n) {
        pasoActual = n;

        $('.wizard-step-content').removeClass('active');
        $(`#step-${n}`).addClass('active');

        $('.wizard-step').removeClass('active completed');
        $('.wizard-step').each(function () {
            const s = parseInt($(this).data('step'), 10);
            if (s < n) $(this).addClass('completed');
            if (s === n) $(this).addClass('active');
        });

        $('#paso-indicador').text(`Paso ${n} de ${TOTAL_PASOS}`);
        $('#wizard-progress-bar').css('width', ((n / TOTAL_PASOS) * 100) + '%');

        $('#btnAnterior').prop('disabled', n === 1);
        $('#btnSiguiente').toggle(n < TOTAL_PASOS);
        $('#btnGuardar').toggle(n === TOTAL_PASOS);

        // Volver arriba de la tarjeta al cambiar de paso
        $('.activity-wizard-card')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function validarPaso(n) {
        limpiarErrores('formSeguimiento');

        if (n === 1) {
            if (!$('#idSegActividad').val()) {
                $('#selectSesion').addClass('is-invalid');
                $('#error-idSegActividad').text('Debe seleccionar una sesión.').show();
                lanzarAviso('Debe seleccionar la sesión que va a registrar.', 'warning');
                return false;
            }
        }

        if (n === 2) {
            let valido = true;
            const logro = $('#logroActividad').val().trim();

            if (logro === '') {
                mostrarError('logroActividad', 'Debe describir el logro de la sesión.');
                valido = false;
            } else if (logro.length < 10) {
                mostrarError('logroActividad', 'El logro debe tener al menos 10 caracteres.');
                valido = false;
            }
            if (!$('#idTipEntrega').val()) {
                mostrarError('idTipEntrega', 'Debe seleccionar el tipo de entrega.');
                valido = false;
            }
            if (!valido) {
                lanzarAviso('Revise los campos marcados del paso Desarrollo.', 'warning');
            }
            return valido;
        }

        return true;
    }

    $('#btnSiguiente').on('click', function () {
        if (validarPaso(pasoActual)) {
            mostrarPaso(pasoActual + 1);
        }
    });

    $('#btnAnterior').on('click', function () {
        if (pasoActual > 1) {
            mostrarPaso(pasoActual - 1);
        }
    });

    // ============================================================
    // CARGA INICIAL DE SESIONES Y BENEFICIARIOS
    // ============================================================
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

            select.append($('<option>', {
                value: s.idSegActividad,
                text: texto,
                disabled: futura // no se registra lo que aún no ocurre
            }));
        });

        // Barra de progreso de la actividad
        const total = sesionesCache.length;
        const registradas = sesionesCache.filter(function (s) {
            return (s.logroActividad || '') !== '';
        }).length;
        const pct = total > 0 ? Math.round((registradas / total) * 100) : 0;
        $('#textoProgreso').text(registradas + '/' + total + ' (' + pct + '%)');
        $('#barraProgreso').css('width', pct + '%')
            .toggleClass('bg-success', pct === 100)
            .toggleClass('bg-primary', pct !== 100);
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
                        '<strong>' + $('<span>').text(b.apellidosBeneficiario).html() + ', ' + $('<span>').text(b.nombresBeneficiario).html() + '</strong>' +
                        ' <span class="text-muted">(' + $('<span>').text(b.cedulaBeneficiario).html() + ')</span>' +
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

    // Contadores de caracteres del paso Desarrollo
    function actualizarContadoresTexto() {
        $('#contadorLogro').text($('#logroActividad').val().length + '/500');
        $('#contadorObserv').text($('#observObstaculo').val().length + '/500');
    }
    $('#logroActividad, #observObstaculo').on('input', actualizarContadoresTexto);

    // Al elegir sesión: muestra su fecha y carga lo ya registrado (edición)
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
            actualizarContadoresTexto();
            return;
        }

        $('#idSegActividad').val(sesion.idSegActividad);
        $('#fechaSesionTexto').val(formatearFecha(sesion.fechaSesion) +
            (sesion.nomTipEntrega ? ' · ' + sesion.nomTipEntrega : ''));
        $('#logroActividad').val(sesion.logroActividad || '');
        $('#observObstaculo').val(sesion.observObstaculo || '');
        $('#idTipEntrega').val(sesion.idTipEntrega || '');
        actualizarContadoresTexto();

        pintarBeneficiarios(asistentesCache[sesion.idSegActividad] || []);
    });

    // Carga inicial (la página vive en esta actividad, sin modal)
    (function cargarDatos() {
        $('#lista-beneficiarios').html(
            '<p class="text-muted small mb-0 px-2 py-3 text-center"><span class="spinner-border spinner-border-sm me-1"></span>Cargando...</p>'
        );

        $.ajax({
            url: 'index.php?action=obtenerSesionesSeguimiento&idActividad=' + encodeURIComponent(idActividadActual),
            type: 'GET',
            dataType: 'json',
            success: function (resp) {
                if (resp.status !== 'success') {
                    lanzarAviso(resp.message || 'No se pudieron cargar las sesiones.', 'danger');
                    $('#selectSesion').html('<option value="">Sin sesiones</option>');
                    $('#lista-beneficiarios').html('<p class="text-muted small mb-0 px-2 py-3 text-center">Sin datos.</p>');
                    return;
                }
                sesionesCache = resp.sesiones || [];
                beneficiariosCache = resp.beneficiarios || [];
                asistentesCache = resp.asistentes || {};
                hoy = resp.hoy || '';

                pintarSesiones();
                pintarBeneficiarios([]);
            },
            error: function () {
                lanzarAviso('No se pudieron cargar los datos de seguimiento.', 'danger');
                $('#selectSesion').html('<option value="">Error de carga</option>');
            }
        });
    })();

    // ============================================================
    // GUARDAR SEGUIMIENTO
    // ============================================================
    $('#formSeguimiento').on('submit', function (e) {
        e.preventDefault();
        limpiarErrores('formSeguimiento');

        let valido = true;

        if (!$('#idSegActividad').val()) {
            mostrarPaso(1);
            $('#selectSesion').addClass('is-invalid');
            $('#error-idSegActividad').text('Debe seleccionar una sesión.').show();
            valido = false;
        }
        const logro = $('#logroActividad').val().trim();
        if (logro === '') {
            mostrarError('logroActividad', 'Debe describir el logro de la sesión.');
            valido = false;
        } else if (logro.length < 10) {
            mostrarError('logroActividad', 'El logro debe tener al menos 10 caracteres.');
            valido = false;
        }
        if (!$('#idTipEntrega').val()) {
            mostrarError('idTipEntrega', 'Debe seleccionar el tipo de entrega.');
            valido = false;
        }

        if (!valido) {
            lanzarAviso('Revise los campos marcados antes de guardar.', 'warning');
            return;
        }

        const asistentes = $('.asistencia-check:checked').map(function () {
            return $(this).val();
        }).get();

        const btnGuardar = $('#btnGuardar');
        btnGuardar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');

        $.ajax({
            url: 'index.php?action=guardarSeguimiento',
            type: 'POST',
            data: {
                idSegActividad: $('#idSegActividad').val().trim(),
                logroActividad: logro,
                observObstaculo: $('#observObstaculo').val().trim(),
                idTipEntrega: $('#idTipEntrega').val(),
                asistentes: asistentes
            },
            dataType: 'json',
            success: function (resp) {
                if (resp.status === 'success') {
                    // El aviso se muestra al volver al listado
                    sessionStorage.setItem('sigpaf_seguimiento_aviso',
                        JSON.stringify({ mensaje: resp.message, tipo: 'success' }));
                    window.location.href = 'index.php?action=seguimiento';
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

    // ============================================================
    // NUEVO BENEFICIARIO RÁPIDO (formulario inline, sin modal)
    // ============================================================
    // El panel de beneficiario es un div (no un form anidado): se guarda
    // con el click del botón propio
    $('#btnGuardarBeneficiario').on('click', function () {
        limpiarErrores('panelFormBeneficiario');

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

        const btn = $('#btnGuardarBeneficiario');
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
                    // Agregar al listado y marcarlo como asistente
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

                    // Cerrar el panel inline y limpiar sus campos
                    bootstrap.Collapse.getInstance(document.getElementById('panelNuevoBeneficiario')).hide();
                    $('#panelFormBeneficiario').find('input, select').val('');
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

    // ============================================================
    // CAMBIAR ESTATUS DE LA ACTIVIDAD (alerta de confirmación)
    // ============================================================
    function nombreDeEstatus(idEstatus) {
        const encontrado = (cfg.estatus || []).find(function (e) {
            return e.idEstatus === idEstatus;
        });
        return encontrado ? encontrado.nomEstatus : idEstatus;
    }

    // Al elegir otro estatus: alerta recordatoria de seguridad antes de aplicar
    $(document).on('change', '#selectEstatusActividad', function () {
        const nuevo = $(this).val();

        // Sin cambio real: no hacer nada
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

    // Cancelar desde la alerta: dejar el selector como estaba
    $('#modalConfirmarEstatus').on('hidden.bs.modal', function () {
        if (estatusPendiente) {
            $('#selectEstatusActividad').val(estatusOriginal);
            estatusPendiente = '';
        }
    });

    // Confirmar el cambio de estatus
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
