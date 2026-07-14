/**
 * SIGPAF - Listado de Actividades
 * Ver detalle, editar, eliminar
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        inicializarEventos();
    });

    function inicializarEventos() {
        // Ver detalle
        document.querySelectorAll('.btn-ver').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                verDetalle(id);
            });
        });

        // Editar
        document.querySelectorAll('.btn-editar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                window.location.href = 'index.php?action=editarActividad&id=' + id;
            });
        });

        // Eliminar
        document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                confirmarEliminar(id);
            });
        });
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
        const l = data.lugar || {};
        const grupos = data.gruposEtarios || [];
        const seguimiento = data.seguimiento || [];

        const badgeEstatus = function(estatus) {
            if (!estatus) return '<span class="badge bg-secondary">Desconocido</span>';
            const est = estatus.toLowerCase();
            if (est.includes('activo') || est.includes('curso')) return '<span class="badge bg-success">' + estatus + '</span>';
            if (est.includes('planif') || est.includes('pendiente')) return '<span class="badge bg-warning text-dark">' + estatus + '</span>';
            if (est.includes('final') || est.includes('complet')) return '<span class="badge bg-info">' + estatus + '</span>';
            return '<span class="badge bg-secondary">' + estatus + '</span>';
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
            '<tr><td class="text-muted">Horario:</td><td>' + (a.nomHorario || '-') + '</td></tr>' +
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
            '<table class="table table-sm table-borderless">' +
            '<tr><td class="text-muted" style="width:40%">Lugar:</td><td>' + (l.nomLugarActividad || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Espacio:</td><td>' + (l.nombreEspacioUtilizar || 'No especificado') + (l.capacidad ? ' (Cap: ' + l.capacidad + ')' : '') + '</td></tr>' +
            '<tr><td class="text-muted">Es Sede:</td><td>' + (l.esSede ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>') + '</td></tr>' +
            '</table>' +
            '</div>' +
            '<div class="col-12">' +
            '<h6 class="text-primary fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Responsables</h6>' +
            '<div class="row">' +
            '<div class="col-md-6"><span class="text-muted">Empleado:</span> <span class="fw-medium">' + (a.nomEmpleado || '-') + '</span></div>' +
            '<div class="col-md-6"><span class="text-muted">Docente:</span> <span class="fw-medium">' + (a.nomDocente || '-') + '</span></div>' +
            '</div>' +
            '</div>' +
            '<div class="col-12">' +
            '<h6 class="text-primary fw-bold mb-3"><i class="bi bi-list-check me-2"></i>Seguimiento de Sesiones</h6>' +
            seguimientoHtml +
            '</div>' +
            '</div>';
    }

    function confirmarEliminar(id) {
        if (!confirm('¿Está seguro de eliminar esta actividad? Esta acción no se puede deshacer.')) {
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?action=eliminarActividad', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const respuesta = JSON.parse(xhr.responseText);
                    if (respuesta.status === 'success') {
                        mostrarAlerta('success', respuesta.message);
                        // Eliminar fila de la tabla
                        const fila = document.querySelector('button[data-id="' + id + '"]').closest('tr');
                        fila.style.transition = 'all 0.3s ease';
                        fila.style.opacity = '0';
                        fila.style.transform = 'translateX(-20px)';
                        setTimeout(function() {
                            fila.remove();
                            // Si no quedan filas, mostrar mensaje vacío
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
    }

    function mostrarAlerta(tipo, mensaje) {
        const alerta = document.getElementById('registro-alerta');
        if (!alerta) return;

        const icono = document.getElementById('alerta-icono');
        const texto = document.getElementById('alerta-texto');

        alerta.className = 'alert alert-' + tipo + ' shadow-sm';
        alerta.style.display = 'block';

        const iconos = {
            success: 'bi-check-circle-fill',
            danger: 'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill'
        };

        icono.className = 'bi ' + (iconos[tipo] || 'bi-info-circle') + ' me-2';
        texto.textContent = mensaje;

        setTimeout(function() {
            alerta.style.display = 'none';
        }, 5000);
    }
})();