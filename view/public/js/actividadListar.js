// Lista de actividades



(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        inicializarEventos();
    });

    function inicializarEventos() {

        document.querySelectorAll('.btn-ver').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                verDetalle(id);
            });
        });


        document.querySelectorAll('.btn-editar:not(.btn-bloqueado)').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                window.location.href = 'index.php?action=editarActividad&id=' + id;
            });
        });


        document.querySelectorAll('.btn-eliminar:not(.btn-bloqueado)').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                confirmarEliminar(id);
            });
        });

        // Botones bloqueados: la actividad fue planificada por otro usuario.
        // Se registra el intento en bitácora y se muestra el mensaje.
        document.querySelectorAll('.btn-bloqueado').forEach(function(btn) {
            btn.addEventListener('click', function() {
                registrarIntentoBloqueado(this.dataset.id, this.dataset.accion || 'modificar');
            });
        });

        // Notas de evaluación (solo visibles para quien planificó la actividad)
        document.querySelectorAll('.btn-notas-eval').forEach(function(btn) {
            btn.addEventListener('click', function() {
                verNotasEvaluacion(this.dataset.id);
            });
        });
    }

    function registrarIntentoBloqueado(id, accion) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?action=registrarIntentoModificacion', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            let mensaje = 'Acción no permitida: esta actividad fue planificada por otro usuario.';
            if (xhr.status === 200) {
                try {
                    const respuesta = JSON.parse(xhr.responseText);
                    mensaje = respuesta.message || mensaje;
                } catch(e) {}
            }
            mostrarAlerta('danger', mensaje);
        };
        xhr.onerror = function() {
            mostrarAlerta('danger', 'Acción no permitida: esta actividad fue planificada por otro usuario.');
        };
        xhr.send('idActividad=' + encodeURIComponent(id) + '&accion=' + encodeURIComponent(accion));
    }

    function verNotasEvaluacion(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalNotasEvaluacion'));
        const content = document.getElementById('notas-evaluacion-content');
        content.dataset.actividad = id;

        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning"></div><p class="text-muted mt-2">Cargando notas...</p></div>';
        modal.show();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'index.php?action=notasEvaluacionActividad&id=' + encodeURIComponent(id), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        renderizarNotas(data);
                    } else {
                        content.innerHTML = '<div class="alert alert-danger mb-0">' + (data.message || 'No se pudieron cargar las notas.') + '</div>';
                    }
                } catch(e) {
                    content.innerHTML = '<div class="alert alert-danger mb-0">Error al cargar las notas.</div>';
                }
            }
        };
        xhr.onerror = function() {
            content.innerHTML = '<div class="alert alert-danger mb-0">Error de conexión.</div>';
        };
        xhr.send();
    }

    function renderizarNotas(data) {
        const content = document.getElementById('notas-evaluacion-content');
        const notas = data.notas || [];
        const nombre = data.nombreActividad || '';

        let html = '<p class="text-muted small mb-3">Actividad: <strong>' + nombre + '</strong>. ' +
            'Estas notas de evaluación solo las puedes ver tú, como planificador de la actividad.</p>';

        if (!notas.length) {
            html += '<div class="alert alert-light border mb-0">No hay notas de evaluación para esta actividad.</div>';
        } else {
            html += '<div class="list-group list-group-flush">';
            notas.forEach(function(n) {
                const fecha = n.fechaEvaluacion ? new Date(n.fechaEvaluacion.replace(' ', 'T')).toLocaleString('es-VE') : '';
                const requiere = parseInt(n.requiereCambio, 10) === 1;
                const confirmado = parseInt(n.confirmadoCambios, 10) === 1;
                const badge = requiere
                    ? '<span class="badge bg-danger">Requiere cambios</span>'
                    : '<span class="badge bg-success">Sin cambios requeridos</span>';

                let pieNota = '';
                if (requiere && confirmado) {
                    const fechaConf = n.fechaConfirmacion ? new Date(n.fechaConfirmacion.replace(' ', 'T')).toLocaleString('es-VE') : '';
                    pieNota = '<div class="mt-2"><span class="badge bg-primary"><i class="bi bi-check2-circle me-1"></i>Cambios confirmados' + (fechaConf ? ' el ' + fechaConf : '') + '</span></div>';
                } else if (requiere) {
                    pieNota =
                        '<div class="form-check form-switch mt-2">' +
                        '<input class="form-check-input switch-confirmar" type="checkbox" role="switch" data-eval="' + n.idEvaluacion + '" id="switch-' + n.idEvaluacion + '">' +
                        '<label class="form-check-label fw-bold text-primary" for="switch-' + n.idEvaluacion + '">Confirmar que realicé los cambios pedidos</label>' +
                        '</div>';
                }

                html +=
                    '<div class="list-group-item px-0">' +
                    '<div class="d-flex justify-content-between align-items-center mb-1">' +
                    badge +
                    '<span class="text-muted small">' + fecha + (n.nomEvaluador ? ' · ' + n.nomEvaluador : '') + '</span>' +
                    '</div>' +
                    '<div>' + (n.notaEvaluacion || '<em class="text-muted">Sin nota</em>') + '</div>' +
                    pieNota +
                    '</div>';
            });
            html += '</div>';
        }
        content.innerHTML = html;

        content.querySelectorAll('.switch-confirmar').forEach(function(sw) {
            sw.addEventListener('change', function() {
                if (this.checked) {
                    confirmarCambiosEvaluacion(this.dataset.eval, this);
                }
            });
        });
    }

    function confirmarCambiosEvaluacion(idEvaluacion, switchEl) {
        switchEl.disabled = true;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?action=confirmarCambiosEvaluacion', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const respuesta = JSON.parse(xhr.responseText);
                    if (respuesta.status === 'success') {
                        const item = switchEl.closest('.list-group-item');
                        const bloque = switchEl.closest('.form-check');
                        if (bloque) {
                            bloque.outerHTML = '<div class="mt-2"><span class="badge bg-primary"><i class="bi bi-check2-circle me-1"></i>Cambios confirmados</span></div>';
                        }
                        mostrarAlerta('success', respuesta.message);
                        // Quitar la notificación de la fila de la actividad si ya no quedan pendientes
                        if (item) {
                            const modal = document.getElementById('notas-evaluacion-content');
                            if (!modal.querySelector('.switch-confirmar')) {
                                const botonCampana = document.querySelector('.btn-notas-eval[data-id="' + modal.dataset.actividad + '"]');
                                if (botonCampana) { botonCampana.remove(); }
                            }
                        }
                    } else {
                        switchEl.checked = false;
                        switchEl.disabled = false;
                        mostrarAlerta('danger', respuesta.message || 'No se pudo confirmar.');
                    }
                } catch(e) {
                    switchEl.checked = false;
                    switchEl.disabled = false;
                    mostrarAlerta('danger', 'Error en la respuesta del servidor.');
                }
            }
        };
        xhr.onerror = function() {
            switchEl.checked = false;
            switchEl.disabled = false;
            mostrarAlerta('danger', 'Error de conexión.');
        };
        xhr.send('idEvaluacion=' + encodeURIComponent(idEvaluacion));
    }

    function verDetalle(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalVerActividad'));
        const content = document.getElementById('detalle-actividad-content');

        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="text-muted mt-2">Cargando...</p></div>';
        modal.show();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'index.php?action=consultarActividad&id=' + encodeURIComponent(id), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    renderizarDetalle(data);
                } catch(e) {
                    content.innerHTML = '<div class="alert alert-danger">Error al cargar los datos</div>';
                }
            }
        };
        xhr.onerror = function() {
            content.innerHTML = '<div class="alert alert-danger">Error de conexión</div>';
        };
        xhr.send();
    }

    function renderizarDetalle(data) {
        const content = document.getElementById('detalle-actividad-content');
        const a = data.actividad || {};
        const lugares = data.lugares || [];
        const horarios = data.horarios || [];
        const empleados = data.empleados || [];
        const docentes = data.docentes || [];
        const grupos = data.gruposEtarios || [];
        const seguimiento = data.seguimiento || [];
        const nombres = function (lista, campo) {
            return lista.map(function (item) { return item[campo] || ''; }).filter(Boolean).join(', ') || '-';
        };
        const lugaresHtml = lugares.length ? lugares.map(function (lugar) {
            const espacio = lugar.nombreEspacioUtilizar ? ' — ' + lugar.nombreEspacioUtilizar +
                (lugar.capacidad ? ' (Capacidad: ' + lugar.capacidad + ')' : '') : '';
            return '<div class="mb-1"><strong>' + (lugar.nomLugarActividad || '-') + '</strong><span class="text-muted">' + espacio + '</span></div>';
        }).join('') : '<span class="text-muted">No especificado</span>';



        const badgeEstatus = function(estatus) {
            if (!estatus) return '<span class="badge bg-secondary">Desconocido</span>';
            const est = estatus.toLowerCase();
            if (est.includes('final')) return '<span class="badge bg-success">' + estatus + '</span>';
            if (est.includes('cancel') || est.includes('espera')) return '<span class="badge bg-danger">' + estatus + '</span>';
            return '<span class="badge bg-warning text-dark">' + estatus + '</span>';
        };

        let gruposHtml = '';
        if (grupos.length > 0) {
            grupos.forEach(function(g) {
                gruposHtml += '<span class="badge bg-info me-1">' + g.nomGrupoEtareo + ' (' + g.edadMin + '-' + g.edadMax + ' años)</span>';
            });
        } else {
            gruposHtml = '<span class="text-muted">No especificado</span>';
        }

        let seguimientoHtml = '';
        if (seguimiento.length > 0) {
            seguimientoHtml = '<div class="list-group list-group-flush">';
            seguimiento.forEach(function(s) {
                const fecha = s.fechaSesion ? new Date(s.fechaSesion).toLocaleDateString('es-VE') : 'Sin fecha';
                seguimientoHtml += 
                    '<div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">' +
                    '<div><span class="badge bg-primary me-2">Sesión ' + s.nroSesionPlanificada + '</span><span class="small">' + fecha + '</span></div>' +
                    '<span class="badge bg-light text-dark border">' + (s.nomTipEntrega || 'Sin tipo') + '</span>' +
                    '</div>';
            });
            seguimientoHtml += '</div>';
        } else {
            seguimientoHtml = '<p class="text-muted small mb-0">No hay sesiones registradas</p>';
        }

        content.innerHTML = 
            '<div class="row g-4">' +
            '<div class="col-md-6">' +
            '<h6 class="text-primary fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Información General</h6>' +
            '<table class="table table-sm table-borderless">' +
            '<tr><td class="text-muted" style="width:35%">Código:</td><td class="fw-bold">' + (a.idActividad || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Nombre:</td><td>' + (a.nombreActividad || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Tipo:</td><td>' + (a.nomTipoActividad || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Vértice:</td><td>' + (a.nombreVertice || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Área:</td><td>' + (a.nomAreaE || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Estrategia:</td><td>' + (a.nomEstDesarrollo || '-') + '</td></tr>' +
            '</table>' +
            '</div>' +
            '<div class="col-md-6">' +
            '<h6 class="text-primary fw-bold mb-3"><i class="bi bi-calendar-event me-2"></i>Fechas</h6>' +
            '<table class="table table-sm table-borderless">' +
            '<tr><td class="text-muted" style="width:40%">Inicio:</td><td>' + (a.fechainicioActividad ? new Date(a.fechainicioActividad).toLocaleDateString('es-VE') : '-') + '</td></tr>' +
            '<tr><td class="text-muted">Fin:</td><td>' + (a.fechafinActividad ? new Date(a.fechafinActividad).toLocaleDateString('es-VE') : '-') + '</td></tr>' +
            '<tr><td class="text-muted">Sesiones:</td><td><span class="badge bg-info">' + (a.cantSesionesPlanificada || 0) + ' planificadas</span></td></tr>' +
            '<tr><td class="text-muted">Horarios:</td><td>' + nombres(horarios, 'nomHorario') + '</td></tr>' +
            '<tr><td class="text-muted">Estatus:</td><td>' + badgeEstatus(a.nomEstatus) + '</td></tr>' +
            '</table>' +
            '</div>' +
            '<div class="col-md-6">' +
            '<h6 class="text-primary fw-bold mb-3"><i class="bi bi-people me-2"></i>Grupos y Capacidad</h6>' +
            '<table class="table table-sm table-borderless">' +
            '<tr><td class="text-muted" style="width:40%">Grupos Etarios:</td><td>' + gruposHtml + '</td></tr>' +
            '<tr><td class="text-muted">Grupo Étnico:</td><td>' + (a.nomGrupoEtnio || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Unidad Medida:</td><td>' + (a.nomUnidadMedida || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Personas:</td><td class="fw-bold">' + (a.cantPersoAtender || 0) + '</td></tr>' +
            '</table>' +
            '</div>' +
            '<div class="col-md-6">' +
            '<h6 class="text-primary fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Ubicación</h6>' +
            lugaresHtml +
            '</div>' +
            '<div class="col-12">' +
            '<h6 class="text-primary fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Responsables</h6>' +
            '<div class="row">' +
            '<div class="col-md-6"><span class="text-muted">Empleados:</span> <span class="fw-medium">' + nombres(empleados, 'nombreCompleto') + '</span></div>' +
            '<div class="col-md-6"><span class="text-muted">Docentes:</span> <span class="fw-medium">' + nombres(docentes, 'nombreCompleto') + '</span></div>' +
            '</div>' +
            '</div>' +
            '<div class="col-12">' +
            '<h6 class="text-primary fw-bold mb-3"><i class="bi bi-list-check me-2"></i>Seguimiento de Sesiones</h6>' +
            seguimientoHtml +
            '</div>' +
            '</div>';
    }

    function confirmarEliminar(id) {
        Validar.confirmar('¿Está seguro de eliminar esta actividad? Esta acción no se puede deshacer.', function() {

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?action=eliminarActividad', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const respuesta = JSON.parse(xhr.responseText);
                    if (respuesta.status === 'success') {
                        mostrarAlerta('success', respuesta.message);

                        const fila = document.querySelector('button[data-id="' + id + '"]').closest('tr');
                        fila.style.transition = 'all 0.3s ease';
                        fila.style.opacity = '0';
                        fila.style.transform = 'translateX(-20px)';
                        setTimeout(function() {
                            fila.remove();

                            const tbody = document.querySelector('table tbody');
                            if (tbody.children.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay actividades registradas actualmente.</td></tr>';
                            }
                        }, 300);
                    } else {
                        mostrarAlerta('danger', respuesta.message || 'Error al eliminar');
                    }
                } catch(e) {
                    mostrarAlerta('danger', 'Error en la respuesta del servidor');
                }
            }
        };
        xhr.send('idActividad=' + encodeURIComponent(id));
        });
    }

    function mostrarAlerta(tipo, mensaje) {
    Validar.aviso(mensaje, tipo);
}
})();