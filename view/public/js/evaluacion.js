// Módulo de evaluación de actividades

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        inicializarEventos();
    });

    function inicializarEventos() {
        document.querySelectorAll('.btn-evaluar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                abrirModalEvaluar(this.dataset.id, this.dataset.nombre || '');
            });
        });

        document.querySelectorAll('.btn-historial').forEach(function(btn) {
            btn.addEventListener('click', function() {
                verHistorial(this.dataset.id);
            });
        });

        const btnGuardar = document.getElementById('btn-guardar-evaluacion');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', guardarEvaluacion);
        }

        const chkRequiere = document.getElementById('eval-requiereCambio');
        if (chkRequiere) {
            chkRequiere.addEventListener('change', function() {
                const ayuda = document.getElementById('eval-nota-ayuda');
                if (ayuda) {
                    ayuda.textContent = this.checked
                        ? 'La nota es obligatoria: indica los cambios que debe hacer el planificador.'
                        : 'La nota es opcional si no se requieren cambios; obligatoria si los requiere.';
                }
            });
        }
    }

    function abrirModalEvaluar(id, nombre) {
        document.getElementById('eval-idActividad').value = id;
        document.getElementById('eval-nombre-actividad').textContent = nombre;
        document.getElementById('eval-requiereCambio').checked = false;
        document.getElementById('eval-nota').value = '';
        ocultarMensaje();
        new bootstrap.Modal(document.getElementById('modalEvaluar')).show();
    }

    function mostrarMensaje(texto, tipo) {
        const caja = document.getElementById('eval-mensaje');
        caja.className = 'alert alert-' + (tipo || 'danger');
        caja.textContent = texto;
        caja.style.display = 'block';
    }

    function ocultarMensaje() {
        const caja = document.getElementById('eval-mensaje');
        caja.style.display = 'none';
        caja.textContent = '';
    }

    function guardarEvaluacion() {
        const idActividad = document.getElementById('eval-idActividad').value;
        const requiereCambio = document.getElementById('eval-requiereCambio').checked;
        const nota = document.getElementById('eval-nota').value.trim();

        if (requiereCambio && nota === '') {
            mostrarMensaje('Si la actividad requiere cambios, debe escribir la nota con las observaciones para el planificador.', 'danger');
            return;
        }

        const btnGuardar = document.getElementById('btn-guardar-evaluacion');
        btnGuardar.disabled = true;

        const params = 'idActividad=' + encodeURIComponent(idActividad) +
            '&notaEvaluacion=' + encodeURIComponent(nota) +
            (requiereCambio ? '&requiereCambio=1' : '');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?action=guardarEvaluacion', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            btnGuardar.disabled = false;
            if (xhr.status === 200) {
                try {
                    const respuesta = JSON.parse(xhr.responseText);
                    if (respuesta.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('modalEvaluar')).hide();
                        Validar.aviso(respuesta.message, 'success');
                        setTimeout(function() { window.location.reload(); }, 1200);
                    } else {
                        mostrarMensaje(respuesta.message || 'Error al registrar la evaluación.', 'danger');
                    }
                } catch(e) {
                    mostrarMensaje('Error en la respuesta del servidor.', 'danger');
                }
            } else {
                mostrarMensaje('Error de conexión con el servidor.', 'danger');
            }
        };
        xhr.onerror = function() {
            btnGuardar.disabled = false;
            mostrarMensaje('Error de conexión con el servidor.', 'danger');
        };
        xhr.send(params);
    }

    function verHistorial(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalHistorialEval'));
        const content = document.getElementById('historial-eval-content');

        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-info"></div><p class="text-muted mt-2">Cargando historial...</p></div>';
        modal.show();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'index.php?action=historialEvaluacion&id=' + encodeURIComponent(id), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        renderizarHistorial(data);
                    } else {
                        content.innerHTML = '<div class="alert alert-danger mb-0">' + (data.message || 'No se pudo cargar el historial.') + '</div>';
                    }
                } catch(e) {
                    content.innerHTML = '<div class="alert alert-danger mb-0">Error al cargar el historial.</div>';
                }
            }
        };
        xhr.onerror = function() {
            content.innerHTML = '<div class="alert alert-danger mb-0">Error de conexión.</div>';
        };
        xhr.send();
    }

    function renderizarHistorial(data) {
        const content = document.getElementById('historial-eval-content');
        const evaluaciones = data.evaluaciones || [];

        let html = '<p class="text-muted small mb-3">Actividad: <strong>' + (data.nombreActividad || '') + '</strong></p>';

        if (!evaluaciones.length) {
            html += '<div class="alert alert-light border mb-0">Esta actividad aún no tiene evaluaciones registradas.</div>';
        } else {
            html += '<div class="list-group list-group-flush">';
            evaluaciones.forEach(function(ev) {
                const fecha = ev.fechaEvaluacion ? new Date(ev.fechaEvaluacion.replace(' ', 'T')).toLocaleString('es-VE') : '';
                const requiere = parseInt(ev.requiereCambio, 10) === 1;
                const confirmado = parseInt(ev.confirmadoCambios, 10) === 1;
                const badge = requiere
                    ? '<span class="badge bg-danger">Requiere cambios</span>'
                    : '<span class="badge bg-success">Sin cambios requeridos</span>';
                let confirmacion = '';
                if (requiere && confirmado) {
                    const fechaConf = ev.fechaConfirmacion ? new Date(ev.fechaConfirmacion.replace(' ', 'T')).toLocaleString('es-VE') : '';
                    confirmacion = '<div class="mt-1"><span class="badge bg-primary"><i class="bi bi-bell-fill me-1"></i>El planificador confirmó los cambios' + (fechaConf ? ' el ' + fechaConf : '') + '</span></div>';
                } else if (requiere) {
                    confirmacion = '<div class="mt-1"><span class="badge bg-warning text-dark">Esperando confirmación del planificador</span></div>';
                }
                html +=
                    '<div class="list-group-item px-0">' +
                    '<div class="d-flex justify-content-between align-items-center mb-1">' +
                    '<span>' + badge + ' <span class="badge bg-secondary ms-1">' + ev.idEvaluacion + '</span></span>' +
                    '<span class="text-muted small">' + fecha + (ev.nomEvaluador ? ' · ' + ev.nomEvaluador : '') + '</span>' +
                    '</div>' +
                    '<div>' + (ev.notaEvaluacion || '<em class="text-muted">Sin nota</em>') + '</div>' +
                    confirmacion +
                    '</div>';
            });
            html += '</div>';
        }
        content.innerHTML = html;
    }
})();
