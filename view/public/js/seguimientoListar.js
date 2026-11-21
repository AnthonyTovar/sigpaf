// Listado de seguimiento: modal de sesiones y ficha de sesión con exportación PDF

$(document).ready(function () {

    function lanzarAviso(mensaje, tipo) {
        Validar.aviso(mensaje, tipo);
    }

    function escapar(texto) {
        return $('<span>').text(texto == null ? '' : String(texto)).html();
    }

    function formatearFecha(fechaStr) {
        if (!fechaStr) return '-';
        const f = new Date(fechaStr + 'T00:00:00');
        return f.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }


    // Modal: sesiones de la actividad

    $(document).on('click', '.btn-ver-sesiones', function () {
        const idActividad = $(this).data('id');
        const nombre = $(this).data('nombre');

        $('#sesiones-nombre-actividad').text(nombre);
        $('#tabla-sesiones-body').html(
            '<tr><td colspan="6" class="text-center py-4 text-muted">' +
            '<span class="spinner-border spinner-border-sm me-1"></span>Cargando sesiones...</td></tr>'
        );

        const modal = new bootstrap.Modal(document.getElementById('modalSesiones'));
        modal.show();

        $.ajax({
            url: 'index.php?action=obtenerSesionesDeActividad&idActividad=' + encodeURIComponent(idActividad),
            type: 'GET',
            dataType: 'json',
            success: function (resp) {
                if (resp.status !== 'success') {
                    $('#tabla-sesiones-body').html(
                        '<tr><td colspan="6" class="text-center py-4 text-muted">' + escapar(resp.message || 'No se pudieron cargar las sesiones.') + '</td></tr>'
                    );
                    return;
                }

                const sesiones = resp.sesiones || [];
                if (sesiones.length === 0) {
                    $('#tabla-sesiones-body').html(
                        '<tr><td colspan="6" class="text-center py-4 text-muted">' +
                        '<i class="bi bi-info-circle me-1"></i>Esta actividad aún no tiene sesiones.</td></tr>'
                    );
                    return;
                }

                let html = '';
                sesiones.forEach(function (s) {
                    const registrada = (s.logroActividad || '') !== '';
                    html +=
                        '<tr>' +
                            '<td class="text-center"><span class="badge bg-secondary px-2 py-1">S' + escapar(s.nroSesionPlanificada) + '</span></td>' +
                            '<td class="text-center small">' + formatearFecha(s.fechaSesion) + '</td>' +
                            '<td class="small">' + escapar(s.nomTipEntrega || 'Sin asignar') + '</td>' +
                            '<td class="text-center"><span class="badge bg-info text-dark"><i class="bi bi-people-fill me-1"></i>' + (parseInt(s.totalAsistentes, 10) || 0) + '</span></td>' +
                            '<td class="text-center">' +
                                (registrada
                                    ? '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Registrada</span>'
                                    : '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pendiente</span>') +
                            '</td>' +
                            '<td class="text-center">' +
                                '<button type="button" class="btn btn-outline-info btn-sm border-0 btn-ficha-sesion" data-id="' + escapar(s.idSegActividad) + '" title="Ver ficha de la sesión">' +
                                    '<i class="bi bi-file-earmark-text"></i>' +
                                '</button>' +
                            '</td>' +
                        '</tr>';
                });
                $('#tabla-sesiones-body').html(html);
            },
            error: function () {
                $('#tabla-sesiones-body').html(
                    '<tr><td colspan="6" class="text-center py-4 text-danger">Error al cargar las sesiones.</td></tr>'
                );
            }
        });
    });


    // Modal: ficha de la sesión

    $(document).on('click', '.btn-ficha-sesion', function () {
        const idSeg = $(this).data('id');

        $('#ficha-sesion-contenido').html(
            '<p class="text-muted text-center py-4"><span class="spinner-border spinner-border-sm me-1"></span>Cargando ficha...</p>'
        );
        $('#btnPdfSesion').attr('href', 'index.php?action=exportarPdfSesion&id=' + encodeURIComponent(idSeg));

        const modal = new bootstrap.Modal(document.getElementById('modalFichaSesion'));
        modal.show();

        $.ajax({
            url: 'index.php?action=obtenerFichaSesion&id=' + encodeURIComponent(idSeg),
            type: 'GET',
            dataType: 'json',
            success: function (resp) {
                if (resp.status !== 'success') {
                    $('#ficha-sesion-contenido').html(
                        '<p class="text-danger text-center py-4">' + escapar(resp.message || 'No se pudo cargar la ficha.') + '</p>'
                    );
                    return;
                }

                const a = resp.actividad || {};
                const s = resp.sesion || {};
                const beneficiarios = resp.beneficiarios || [];
                const registrada = (s.logroActividad || '') !== '';

                let filasResumen = '';
                function filaResumen(etiqueta, valor) {
                    return '<tr>' +
                        '<td class="text-muted text-nowrap align-top" style="width:180px;">' + etiqueta + '</td>' +
                        '<td class="fw-semibold text-dark">' + valor + '</td>' +
                    '</tr>';
                }

                filasResumen += filaResumen('Actividad', escapar(a.nombreActividad));
                filasResumen += filaResumen('Tipo de actividad', escapar(a.nomTipoActividad || '-'));
                filasResumen += filaResumen('Objetivo', '<span class="fw-normal">' + escapar(a.objetivoActividad || '-') + '</span>');
                filasResumen += filaResumen('Sesión N°', escapar(s.nroSesionPlanificada));
                filasResumen += filaResumen('Fecha de la sesión', formatearFecha(s.fechaSesion));
                filasResumen += filaResumen('Tipo de entrega', escapar(s.nomTipEntrega || 'Sin asignar'));
                filasResumen += filaResumen('Asistentes', String(beneficiarios.length));
                filasResumen += filaResumen('Logro de la sesión',
                    registrada ? '<span class="fw-normal">' + escapar(s.logroActividad) + '</span>'
                               : '<em class="text-muted fw-normal">Sesión sin seguimiento registrado.</em>');
                if ((s.observObstaculo || '') !== '') {
                    filasResumen += filaResumen('Observaciones / obstáculos', '<span class="fw-normal">' + escapar(s.observObstaculo) + '</span>');
                }

                let html =
                    '<div class="border rounded mb-3 overflow-hidden">' +
                        '<div class="px-3 py-2 bg-primary text-white small fw-bold">' +
                            '<i class="bi bi-journal-text me-1"></i>Resumen de la Sesión' +
                        '</div>' +
                        '<table class="table table-sm mb-0" style="background:#f8fafc;">' +
                            '<tbody>' + filasResumen + '</tbody>' +
                        '</table>' +
                    '</div>';

                html +=
                    '<h6 class="fw-bold text-primary mb-2"><i class="bi bi-people-fill me-1"></i>Beneficiarios de la sesión (' + beneficiarios.length + ')</h6>';

                if (beneficiarios.length === 0) {
                    html += '<p class="text-muted small text-center py-3 mb-0"><i class="bi bi-inbox me-1"></i>Esta sesión no tiene beneficiarios registrados.</p>';
                } else {
                    html +=
                        '<div class="table-responsive" style="max-height: 320px; overflow-y: auto;">' +
                            '<table class="table table-sm table-hover align-middle mb-0">' +
                                '<thead class="table-light"><tr>' +
                                    '<th class="text-center" style="width:40px;">#</th>' +
                                    '<th>Cédula</th><th>Apellidos</th><th>Nombres</th>' +
                                    '<th class="text-center">Sexo</th><th class="text-center">Fecha Nac.</th>' +
                                '</tr></thead><tbody>';
                    beneficiarios.forEach(function (b, i) {
                        html +=
                            '<tr>' +
                                '<td class="text-center text-muted">' + (i + 1) + '</td>' +
                                '<td>' + escapar(b.cedulaBeneficiario) + '</td>' +
                                '<td>' + escapar(b.apellidosBeneficiario) + '</td>' +
                                '<td>' + escapar(b.nombresBeneficiario) + '</td>' +
                                '<td class="text-center">' + escapar(b.sexoBeneficiario || '-') + '</td>' +
                                '<td class="text-center">' + (b.fechaNacBeneficiario ? formatearFecha(b.fechaNacBeneficiario) : '-') + '</td>' +
                            '</tr>';
                    });
                    html += '</tbody></table></div>';
                }

                $('#ficha-sesion-contenido').html(html);
            },
            error: function () {
                $('#ficha-sesion-contenido').html(
                    '<p class="text-danger text-center py-4">Error al cargar la ficha de la sesión.</p>'
                );
            }
        });
    });


    // Al cerrar la ficha sobre el modal de sesiones, mantener el scroll del body
    $('#modalFichaSesion').on('hidden.bs.modal', function () {
        if ($('#modalSesiones').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });
});
