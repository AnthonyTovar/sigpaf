/**
 * VALIDACIONES UNIFICADAS — SIGPAF
 *
 * Punto único de validación y alertas para todo el sistema.
 * Cualquier módulo JS debe usar estas funciones para que el usuario
 * vea SIEMPRE el mismo efecto, sin importar en qué pantalla esté.
 *
 * Requiere jQuery (cargado en el Layout) y el bloque de alerta estándar
 * que ya tienen todas las vistas:
 *   <div id="registro-alerta"><i id="alerta-icono"></i><span id="alerta-texto"></span></div>
 *
 * Uso:
 *   Validar.aviso('Guardado correctamente', 'success');
 *   Validar.aviso('Faltan campos', 'danger');
 *   if (!Validar.requerido(valor)) Validar.errorCampo('nombre', 'Campo obligatorio');
 */
(function (global) {
    'use strict';

    var Validar = {

        // ===== CONSTANTES COMPARTIDAS =====
        MAX_TEXTO: 300,          // objetivo, descripción, observación
        DURACION_AVISO_MS: 4000, // tiempo visible del aviso
        // Iconos estándar por tipo (Bootstrap Icons)
        ICONOS: {
            success: 'bi-check-circle-fill',
            danger: 'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        },

        // ===== AVISO GLOBAL (alerta superior) =====
        // ÚNICA forma de lanzar alertas en el sistema. Todos los módulos
        // deben delegar aquí para que el efecto sea idéntico en toda la app.
        aviso: function (mensaje, tipo) {
            tipo = tipo || 'info';

            var alerta = document.getElementById('registro-alerta');
            if (!alerta) {
                console.error('[Validar] No existe #registro-alerta en la vista');
                return;
            }

            var icono = document.getElementById('alerta-icono');
            var texto = document.getElementById('alerta-texto');

            alerta.className = 'alert alert-' + tipo + ' shadow-sm';
            alerta.style.display = 'block';

            if (icono) {
                icono.className = 'bi ' + (Validar.ICONOS[tipo] || Validar.ICONOS.info) + ' me-2';
            }
            if (texto) {
                texto.textContent = mensaje;
            }

            clearTimeout(alerta._timerValidar);
            alerta._timerValidar = setTimeout(function () {
                alerta.style.display = 'none';
            }, Validar.DURACION_AVISO_MS);
        },

        // ===== CONFIRMACIÓN ESTILIZADA =====
        // Reemplaza al confirm() nativo del navegador (ese cuadro negro
        // feo del sistema operativo que se veía al eliminar) por un modal
        // con el diseño del sistema. Un solo punto de confirmación para
        // todo SIGPAF.
        // Uso:  Validar.confirmar('¿Eliminar este registro?', function () { ... });
        // Opcional: Validar.confirmar(msg, fn, { titulo, botonSi, peligroso })
        confirmar: function (mensaje, alAceptar, opciones) {
            opciones = opciones || {};
            var titulo = opciones.titulo || 'Confirmar acción';
            var botonSi = opciones.botonSi || 'Sí, continuar';
            var peligroso = opciones.peligroso !== false; // por defecto estilo peligroso

            var modalEl = document.getElementById('validar-modal-confirmar');
            if (!modalEl) {
                modalEl = document.createElement('div');
                modalEl.className = 'modal fade';
                modalEl.id = 'validar-modal-confirmar';
                modalEl.tabIndex = -1;
                modalEl.setAttribute('aria-hidden', 'true');
                modalEl.innerHTML =
                    '<div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">' +
                    '  <div class="modal-content border-0 shadow">' +
                    '    <div class="modal-header text-white" style="background: #1e293b;">' +
                    '      <h5 class="modal-title fs-6"><i class="bi bi-question-circle-fill me-2"></i><span id="vc-titulo"></span></h5>' +
                    '      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                    '    </div>' +
                    '    <div class="modal-body p-4 text-secondary" id="vc-mensaje" style="font-size: 0.92rem; line-height: 1.45;"></div>' +
                    '    <div class="modal-footer border-0 pt-0">' +
                    '      <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>' +
                    '      <button type="button" class="btn btn-sm btn-danger" id="vc-si"><i class="bi bi-trash3-fill me-1"></i><span id="vc-si-texto"></span></button>' +
                    '    </div>' +
                    '  </div>' +
                    '</div>';
                document.body.appendChild(modalEl);
            }

            modalEl.querySelector('#vc-titulo').textContent = titulo;
            modalEl.querySelector('#vc-mensaje').textContent = mensaje;
            modalEl.querySelector('#vc-si-texto').textContent = botonSi;
            var btnSi = modalEl.querySelector('#vc-si');
            btnSi.className = 'btn btn-sm ' + (peligroso ? 'btn-danger' : 'btn-primary');
            btnSi.onclick = function () {
                var instancia = bootstrap.Modal.getInstance(modalEl);
                if (instancia) {
                    instancia.hide();
                }
                if (typeof alAceptar === 'function') {
                    alAceptar();
                }
            };

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        },

        // ===== ERROR EN CAMPO (is-invalid + mensaje debajo) =====
        // name = atributo "name" del campo. Requiere <div class="invalid-feedback" id="error-<name>">.
        errorCampo: function (name, mensaje) {
            var $input = jQuery('[name="' + name + '"]');
            var $error = jQuery('#error-' + name);

            $input.addClass('is-invalid');
            if ($error.length) {
                $error.text(mensaje).show();
            }
        },

        // ===== LIMPIAR ERRORES DE UN FORMULARIO =====
        limpiarErrores: function (formId) {
            if (formId) {
                jQuery('#' + formId + ' input, #' + formId + ' select, #' + formId + ' textarea').removeClass('is-invalid');
                jQuery('#' + formId + ' .invalid-feedback').text('').hide();
            } else {
                jQuery('.is-invalid').removeClass('is-invalid');
                jQuery('.invalid-feedback').text('').hide();
            }
        },

        // ===== REGLAS DE VALIDACIÓN =====
        // Cada regla devuelve TRUE si el valor es VÁLIDO.

        requerido: function (valor) {
            return valor !== undefined && valor !== null && String(valor).trim() !== '';
        },

        // Cumple el máximo de caracteres permitido (por defecto 300)
        longitudValida: function (valor, max) {
            max = max || Validar.MAX_TEXTO;
            if (!valor) return true;
            return String(valor).trim().length <= max;
        },

        // Cédula venezolana: prefijo V/E + 6 a 9 dígitos (o solo 6-9 dígitos)
        cedula: function (valor) {
            if (!valor) return false;
            return /^[VE]?\d{6,9}$/i.test(String(valor).trim());
        },

        // Teléfono venezolano: 0412-1234567 (el guion es opcional)
        telefono: function (valor) {
            if (!valor) return true; // opcional en la mayoría de módulos
            return /^(0(412|414|416|424|426|422))-?\d{7}$/.test(String(valor).replace(/\s/g, ''));
        },

        correo: function (valor) {
            if (!valor) return true; // opcional
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(valor).trim());
        },

        // Fecha válida (YYYY-MM-DD) y no futura para fechas de nacimiento
        fecha: function (valor) {
            if (!valor) return false;
            var dt = new Date(valor + 'T00:00:00');
            return !isNaN(dt.getTime());
        },

        fechaNacimiento: function (valor, edadMin, edadMax) {
            if (!Validar.fecha(valor)) return false;
            edadMin = edadMin || 15;
            edadMax = edadMax || 100;

            var hoy = new Date();
            var nac = new Date(valor + 'T00:00:00');
            var edad = hoy.getFullYear() - nac.getFullYear();
            var mes = hoy.getMonth() - nac.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < nac.getDate())) {
                edad--;
            }
            return edad >= edadMin && edad <= edadMax;
        },

        // Rango de fechas: inicio no posterior al fin
        rangoFechas: function (fechaInicio, fechaFin) {
            if (!Validar.fecha(fechaInicio) || !Validar.fecha(fechaFin)) return false;
            return fechaInicio <= fechaFin;
        },

        // Número entero mayor o igual a un mínimo
        enteroMin: function (valor, min) {
            var n = parseInt(valor, 10);
            if (isNaN(n)) return false;
            return n >= (min || 0);
        },

        // Texto sin números (nombres, apellidos)
        soloLetras: function (valor) {
            if (!valor) return false;
            return /^[A-Za-zÁáÉéÍíÓóÚúÜüÑñ' .-]+$/.test(String(valor).trim());
        }
    };

    // ============================================================
    // TOPE DE CARACTERES EN VIVO (global)
    // Cualquier <input maxlength="N"> o <textarea maxlength="N">
    // del sistema se recorta al instante, aunque se pegue texto.
    // Así el usuario nunca escribe "de más" y recibe el error
    // solo al guardar: lo ve cortado mientras escribe.
    // ============================================================
    function aplicarTopeEnVivo(evt) {
        var el = evt.target;
        if (!el || (el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA')) return;
        var max = parseInt(el.getAttribute('maxlength'), 10);
        if (isNaN(max) || max <= 0) return;
        if (el.value.length > max) {
            el.value = el.value.substring(0, max);
        }
    }
    if (document.addEventListener) {
        // 'input' cubre teclado, pegar y autocompletar
        document.addEventListener('input', aplicarTopeEnVivo, true);
        // 'change' cubre arrastrar/autollenado en navegadores viejos
        document.addEventListener('change', aplicarTopeEnVivo, true);
    }

    global.Validar = Validar;
})(window);
