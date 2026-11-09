// Registro de seguimiento

$(document).ready(function () {

    const cfg = window.SeguimientoNuevoCfg || {};
    const idActividadActual = cfg.idActividad || '';
    let estatusOriginal = cfg.estatusOriginal || '';

    let sesionesCache = [];
    let asistentesCache = {};
    let beneficiariosCache = [];
    let rangosEtariosCache = [];
    let hoy = '';


    let estatusPendiente = '';


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

    function calcularEdad(fechaNac, fechaReferencia) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(fechaNac || '') || !/^\d{4}-\d{2}-\d{2}$/.test(fechaReferencia || '')) {
            return null;
        }

        const nacPartes = fechaNac.split('-').map(Number);
        const refPartes = fechaReferencia.split('-').map(Number);
        const nacimiento = new Date(nacPartes[0], nacPartes[1] - 1, nacPartes[2]);
        const referencia = new Date(refPartes[0], refPartes[1] - 1, refPartes[2]);
        if (nacimiento.getFullYear() !== nacPartes[0] || nacimiento.getMonth() !== nacPartes[1] - 1 ||
            nacimiento.getDate() !== nacPartes[2] || nacimiento > referencia) {
            return null;
        }

        let edad = refPartes[0] - nacPartes[0];
        if (refPartes[1] < nacPartes[1] ||
            (refPartes[1] === nacPartes[1] && refPartes[2] < nacPartes[2])) {
            edad--;
        }
        return edad;
    }

    function edadEnRangos(edad) {
        return rangosEtariosCache.some(function (rango) {
            return edad >= Number(rango.edadMin) && edad <= Number(rango.edadMax);
        });
    }

    function textoRangosEtarios() {
        const textos = {};
        rangosEtariosCache.forEach(function (rango) {
            const clave = rango.edadMin + '-' + rango.edadMax;
            textos[clave] = rango.nomGrupoEtareo + ' (' + Number(rango.edadMin) + ' a ' + Number(rango.edadMax) + ' años)';
        });
        return Object.keys(textos).map(function (clave) { return textos[clave]; }).join(', ');
    }

    function fechaCalculoEdad() {
        const idSeg = $('#idSegActividad').val();
        const sesion = sesionesCache.find(function (s) { return s.idSegActividad === idSeg; });
        return sesion ? sesion.fechaSesion : hoy;
    }

    function errorEdad(fechaNac, fechaReferencia) {
        if (!fechaNac) return 'Debe indicar la fecha de nacimiento para comprobar la edad.';
        const edad = calcularEdad(fechaNac, fechaReferencia);
        if (edad === null) return 'La fecha de nacimiento es inválida o posterior a la sesión.';
        if (rangosEtariosCache.length === 0) return 'La actividad no tiene grupos de edad configurados.';
        if (!edadEnRangos(edad)) {
            return 'Tiene ' + edad + ' años en la fecha de la sesión. Rangos permitidos: ' + textoRangosEtarios() + '.';
        }
        return '';
    }


    // Navegación del asistente

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


    // Carga inicial de sesiones y beneficiarios

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
                disabled: futura
            }));
        });


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
        const fechaReferencia = fechaCalculoEdad();
        $('#textoRangoEdad').text(textoRangosEtarios() || 'No configurado');

        if (beneficiariosCache.length === 0) {
            cont.html('<p class="text-muted small mb-0 px-2 py-3 text-center"><i class="bi bi-inbox me-1"></i>No hay beneficiarios registrados. Agregue uno con el botón verde.</p>');
            $('#contadorAsistentes').text('0');
            return;
        }

        let html = '';
        beneficiariosCache.forEach(function (b) {
            const marcado = marcados.indexOf(b.idBeneficiario) !== -1;
            const edad = calcularEdad(b.fechaNacBeneficiario, fechaReferencia);
            const valido = errorEdad(b.fechaNacBeneficiario, fechaReferencia) === '';
            let avisoEdad = '';
            if (!valido) {
                const textoAviso = !b.fechaNacBeneficiario ? 'Sin fecha de nacimiento' :
                    (edad === null ? 'Fecha inválida' : 'Fuera del rango');
                avisoEdad = ' <span class="badge bg-warning text-dark">' + textoAviso + '</span>';
            }
            const textoEdad = edad === null ? 'Edad no disponible' : edad + ' años';

            html +=
                '<div class="form-check beneficiario-item">' +
                    '<input class="form-check-input asistencia-check" type="checkbox" ' +
                        'value="' + b.idBeneficiario + '" id="ben-' + b.idBeneficiario + '" ' +
                        'data-edad-valida="' + (valido ? '1' : '0') + '" ' + (marcado ? 'checked' : '') + '>' +
                    '<label class="form-check-label small" for="ben-' + b.idBeneficiario + '">' +
                        '<strong>' + $('<span>').text(b.apellidosBeneficiario).html() + ', ' + $('<span>').text(b.nombresBeneficiario).html() + '</strong>' +
                        ' <span class="text-muted">(' + $('<span>').text(b.cedulaBeneficiario).html() + ') · ' + textoEdad + '</span>' +
                        avisoEdad +
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

    $(document).on('change', '.asistencia-check', function () {
        const checkbox = $(this);
        if (checkbox.is(':checked') && checkbox.attr('data-edad-valida') !== '1') {
            checkbox.prop('checked', false);
            const beneficiario = beneficiariosCache.find(function (b) {
                return b.idBeneficiario === checkbox.val();
            });
            const mensaje = beneficiario ? errorEdad(beneficiario.fechaNacBeneficiario, fechaCalculoEdad()) :
                'El beneficiario no cumple el rango de edad de la actividad.';
            lanzarAviso('No se puede incluir al beneficiario. ' + mensaje, 'warning');
        }
        actualizarContador();
    });


    function actualizarContadoresTexto() {
        $('#contadorLogro').text($('#logroActividad').val().length + '/500');
        $('#contadorObserv').text($('#observObstaculo').val().length + '/500');
    }
    $('#logroActividad, #observObstaculo').on('input', actualizarContadoresTexto);


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
                rangosEtariosCache = resp.gruposEtarios || [];
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


    // Guardar seguimiento

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
        if ($('.asistencia-check:checked[data-edad-valida="0"]').length > 0) {
            lanzarAviso('Hay beneficiarios seleccionados fuera del rango de edad. Desmárquelos antes de guardar.', 'warning');
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






    // Registro de beneficiarios por lotes (páginas de 10)

    const TAMANO_PAGINA_BEN = 10;

    function filaLoteBeneficiario() {
        return '<tr class="fila-lote-ben">' +
            '<td><input type="text" class="form-control form-control-sm ben-cedula" placeholder="V12345678" maxlength="9"></td>' +
            '<td><select class="form-select form-select-sm ben-nacionalidad">' +
                '<option value="">Seleccione...</option>' +
                '<option value="Venezolano">Venezolano</option>' +
                '<option value="Extranjero">Extranjero</option>' +
            '</select></td>' +
            '<td><input type="text" class="form-control form-control-sm ben-nombres" placeholder="Nombres" maxlength="250"></td>' +
            '<td><input type="text" class="form-control form-control-sm ben-apellidos" placeholder="Apellidos" maxlength="250"></td>' +
            '<td><input type="date" class="form-control form-control-sm ben-fechanac"></td>' +
            '<td><select class="form-select form-select-sm ben-sexo">' +
                '<option value="">Seleccione...</option>' +
                '<option value="M">M</option>' +
                '<option value="F">F</option>' +
            '</select></td>' +
        '</tr>';
    }

    function agregarFilasLote(cantidad) {
        const tbody = $('#lote-beneficiarios-body');
        for (let i = 0; i < cantidad; i++) {
            tbody.append(filaLoteBeneficiario());
        }
        $('#contadorFilasLote').text(tbody.find('tr').length + ' filas');
    }

    function asegurarFilasMinimas() {
        const tbody = $('#lote-beneficiarios-body');
        if (tbody.find('tr').length < TAMANO_PAGINA_BEN) {
            agregarFilasLote(TAMANO_PAGINA_BEN - tbody.find('tr').length);
        }
    }

    $('#btnMasFilasBeneficiario').on('click', function () {
        agregarFilasLote(TAMANO_PAGINA_BEN);
    });

    // Cargar la primera página de 10 al abrir el panel
    $('#panelNuevoBeneficiario').on('show.bs.collapse', asegurarFilasMinimas);

    // Filtros de formato en vivo: cédula sin letras (salvo prefijo V/E), nombres/apellidos sin números
    $(document).on('input', '.ben-cedula', function () {
        this.value = this.value.toUpperCase().replace(/[^VE0-9]/g, '')
            .replace(/^([VE])?/, '$1').replace(/(?!^)[VE]/g, '').slice(0, 9);
    });
    $(document).on('input', '.ben-nombres, .ben-apellidos', function () {
        this.value = this.value.replace(/[0-9]/g, '');
    });

    // Mensaje inline del panel de beneficiarios (validación y resultados)
    function mensajeLote(texto, tipo) {
        const caja = $('#mensajeLoteBeneficiarios');
        if (!texto) {
            caja.hide().text('').removeClass('alert-warning alert-danger alert-success alert-info');
            return;
        }
        caja.removeClass('alert-warning alert-danger alert-success alert-info')
            .addClass('alert-' + tipo)
            .html('<i class="bi bi-info-circle me-1"></i>' + texto)
            .show();
    }

    function leerLoteBeneficiarios() {
        const filas = [];
        const erroresFilas = [];
        let valido = true;

        $('#lote-beneficiarios-body tr').each(function (indice) {
            const fila = $(this);
            const cedula = fila.find('.ben-cedula').val().trim().toUpperCase();
            const nacionalidad = fila.find('.ben-nacionalidad').val();
            const nombres = fila.find('.ben-nombres').val().trim();
            const apellidos = fila.find('.ben-apellidos').val().trim();
            const fechaNac = fila.find('.ben-fechanac').val();
            const sexo = fila.find('.ben-sexo').val();

            fila.find('input, select').removeClass('is-invalid');

            const vacia = !cedula && !nombres && !apellidos && !nacionalidad && !fechaNac && !sexo;
            if (vacia) return; // las filas vacías se ignoran

            let errorFila = '';
            if (!/^[VE]\d{6,9}$/.test(cedula)) {
                fila.find('.ben-cedula').addClass('is-invalid');
                errorFila = 'cédula inválida (formato V12345678)';
            }
            if (!nacionalidad) { fila.find('.ben-nacionalidad').addClass('is-invalid'); errorFila = errorFila || 'falta la nacionalidad'; }
            if (!nombres) { fila.find('.ben-nombres').addClass('is-invalid'); errorFila = errorFila || 'faltan los nombres'; }
            if (!apellidos) { fila.find('.ben-apellidos').addClass('is-invalid'); errorFila = errorFila || 'faltan los apellidos'; }
            if (!sexo) { fila.find('.ben-sexo').addClass('is-invalid'); errorFila = errorFila || 'falta el sexo'; }
            const errorRango = errorEdad(fechaNac, fechaCalculoEdad());
            if (errorRango) {
                fila.find('.ben-fechanac').addClass('is-invalid');
                errorFila = errorFila || errorRango.replace(/\.$/, '');
            }

            if (errorFila) {
                valido = false;
                erroresFilas.push('Fila ' + (indice + 1) + ': ' + errorFila + '.');
                return;
            }

            filas.push({
                cedula: cedula,
                nacionalidad: nacionalidad,
                nombres: nombres,
                apellidos: apellidos,
                fechaNac: fechaNac,
                sexo: sexo
            });
        });

        if (erroresFilas.length > 0) {
            mensajeLote('Revise la lista antes de guardar:<br>• ' + erroresFilas.join('<br>• '), 'warning');
        } else {
            mensajeLote(null);
        }

        return { valido: valido, filas: filas };
    }

    $('#btnGuardarLoteBeneficiarios').on('click', function () {
        const lote = leerLoteBeneficiarios();
        if (!lote.valido) return;
        if (lote.filas.length === 0) {
            mensajeLote('La página está vacía: complete al menos una fila de la lista para poder guardarla.', 'warning');
            lanzarAviso('Complete al menos una fila para guardar la página.', 'warning');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando página...');

        $.ajax({
            url: 'index.php?action=guardarBeneficiariosLote',
            type: 'POST',
            data: {
                idSegActividad: $('#idSegActividad').val(),
                beneficiarios: lote.filas
            },
            dataType: 'json',
            success: function (resp) {
                if (resp.status === 'success') {
                    const marcados = $('.asistencia-check:checked').map(function () {
                        return $(this).val();
                    }).get();

                    (resp.guardados || []).forEach(function (b) {
                        beneficiariosCache.push(b);
                        marcados.push(b.idBeneficiario);
                    });
                    pintarBeneficiarios(marcados);

                    // Quitar las filas guardadas y garantizar 10 filas limpias
                    $('#lote-beneficiarios-body tr').each(function () {
                        const fila = $(this);
                        const cedula = fila.find('.ben-cedula').val().trim().toUpperCase();
                        if (cedula && (resp.cedulasGuardadas || []).indexOf(cedula) !== -1) {
                            fila.remove();
                        }
                    });
                    asegurarFilasMinimas();

                    let mensaje = resp.message;
                    if ((resp.duplicados || []).length > 0) {
                        mensaje += ' Omitidas por duplicadas: ' + resp.duplicados.join(', ') + '.';
                    }
                    mensajeLote(mensaje, (resp.guardados || []).length > 0 ? 'success' : 'warning');
                    lanzarAviso(mensaje, (resp.guardados || []).length > 0 ? 'success' : 'warning');
                } else {
                    lanzarAviso(resp.message, 'danger');
                }
            },
            error: function () {
                lanzarAviso('Error al registrar la página de beneficiarios.', 'danger');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Guardar página de beneficiarios');
            }
        });
    });





    function nombreDeEstatus(idEstatus) {
        const encontrado = (cfg.estatus || []).find(function (e) {
            return e.idEstatus === idEstatus;
        });
        return encontrado ? encontrado.nomEstatus : idEstatus;
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
