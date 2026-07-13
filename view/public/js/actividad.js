/**
 * SIGPAF - Módulo de Actividades
 * Wizard de registro con calendario interactivo y resumen lateral
 */

(function() {
    'use strict';

    // ===== ESTADO DEL WIZARD =====
    let pasoActual = 1;
    const totalPasos = 6;
    let calendarioMesActual = new Date().getMonth();
    let calendarioAnioActual = new Date().getFullYear();
    let fechaInicioSeleccionada = null;
    let fechaFinSeleccionada = null;
    let fechasSesionesGeneradas = [];

    // ===== INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', function() {
        inicializarWizard();
        inicializarCalendario();
        inicializarEventos();
        inicializarResumenLateral();
    });

    // ===== NAVEGACIÓN DEL WIZARD =====
    function inicializarWizard() {
        const btnSiguiente = document.getElementById('btnSiguiente');
        const btnAnterior = document.getElementById('btnAnterior');
        const btnGuardar = document.getElementById('btnGuardar');

        if (btnSiguiente) {
            btnSiguiente.addEventListener('click', function() {
                if (validarPaso(pasoActual)) {
                    if (pasoActual < totalPasos) {
                        irAPaso(pasoActual + 1);
                    }
                }
            });
        }

        if (btnAnterior) {
            btnAnterior.addEventListener('click', function() {
                if (pasoActual > 1) {
                    irAPaso(pasoActual - 1);
                }
            });
        }

        if (btnGuardar) {
            btnGuardar.addEventListener('click', function(e) {
                e.preventDefault();
                guardarActividad();
            });
        }

        // Click en los círculos de paso
        document.querySelectorAll('.wizard-step').forEach(function(step) {
            step.addEventListener('click', function() {
                const targetPaso = parseInt(this.dataset.step);
                if (targetPaso < pasoActual || puedeNavegarA(targetPaso)) {
                    irAPaso(targetPaso);
                }
            });
        });
    }

    function irAPaso(paso) {
        // Ocultar paso actual
        document.getElementById('step-' + pasoActual).classList.remove('active');
        document.querySelector('.wizard-step[data-step="' + pasoActual + '"]').classList.remove('active');

        // Marcar pasos anteriores como completados
        for (let i = 1; i < paso; i++) {
            document.querySelector('.wizard-step[data-step="' + i + '"]').classList.add('completed');
        }
        for (let i = paso; i <= totalPasos; i++) {
            document.querySelector('.wizard-step[data-step="' + i + '"]').classList.remove('completed');
        }

        // Mostrar nuevo paso
        pasoActual = paso;
        document.getElementById('step-' + pasoActual).classList.add('active');
        document.querySelector('.wizard-step[data-step="' + pasoActual + '"]').classList.add('active');

        // Actualizar indicador y progress bar
        document.getElementById('paso-indicador').textContent = 'Paso ' + pasoActual + ' de ' + totalPasos;
        document.getElementById('wizard-progress-bar').style.width = ((pasoActual / totalPasos) * 100) + '%';

        // Actualizar botones
        document.getElementById('btnAnterior').disabled = (pasoActual === 1);

        if (pasoActual === totalPasos) {
            document.getElementById('btnSiguiente').style.display = 'none';
            document.getElementById('btnGuardar').style.display = 'inline-block';
            generarResumenFinal();
        } else {
            document.getElementById('btnSiguiente').style.display = 'inline-block';
            document.getElementById('btnGuardar').style.display = 'none';
        }

        // Actualizar resumen lateral
        actualizarResumenLateral();
    }

    function puedeNavegarA(paso) {
        // Permitir navegar si todos los pasos anteriores están validados
        for (let i = 1; i < paso; i++) {
            if (!validarPasoSilencioso(i)) return false;
        }
        return true;
    }

    // ===== VALIDACIÓN POR PASO =====
    function validarPaso(paso) {
        let valido = true;
        limpiarErrores();

        switch(paso) {
            case 1:
                valido = validarCampo('nombreActividad', 'El nombre es obligatorio') && valido;
                valido = validarCampo('objetivoActividad', 'El objetivo es obligatorio') && valido;
                valido = validarCampo('descActividad', 'La descripción es obligatoria') && valido;
                valido = validarSelect('idEstDesarrollo', 'Seleccione una estrategia') && valido;
                valido = validarSelect('idTipoActividad', 'Seleccione un tipo') && valido;
                valido = validarSelect('idVertice', 'Seleccione un vértice') && valido;
                valido = validarSelect('idAreaE', 'Seleccione un área') && valido;
                break;

            case 2:
                const gruposCheck = document.querySelectorAll('input[name="gruposEtarios[]"]:checked');
                if (gruposCheck.length === 0) {
                    mostrarError('gruposEtarios', 'Seleccione al menos un grupo etario');
                    valido = false;
                }
                valido = validarSelect('idGrupoEtnio', 'Seleccione un grupo étnico') && valido;
                valido = validarSelect('idUnidadMedida', 'Seleccione una unidad de medida') && valido;
                valido = validarCampo('cantPersoAtender', 'Ingrese la cantidad de personas') && valido;
                break;

            case 3:
                valido = validarCampo('fechainicioActividad', 'Seleccione fecha de inicio') && valido;
                valido = validarCampo('fechafinActividad', 'Seleccione fecha de fin') && valido;
                valido = validarCampo('cantSesionesPlanificada', 'Ingrese cantidad de sesiones') && valido;

                const fechaIni = document.querySelector('input[name="fechainicioActividad"]').value;
                const fechaFin = document.querySelector('input[name="fechafinActividad"]').value;
                if (fechaIni && fechaFin && fechaIni > fechaFin) {
                    mostrarError('fechafinActividad', 'La fecha de fin debe ser posterior a la de inicio');
                    valido = false;
                }
                break;

            case 4:
                valido = validarSelect('idLugarActividad', 'Seleccione un lugar') && valido;
                const espacioVisible = document.getElementById('espacio-container').style.display !== 'none';
                if (espacioVisible) {
                    valido = validarSelect('idEspacioUtilizar', 'Seleccione un espacio') && valido;
                }
                valido = validarCampo('idHorario', 'Seleccione un horario') && valido;
                break;

            case 5:
                valido = validarSelect('idEmpleado', 'Seleccione un empleado responsable') && valido;
                valido = validarSelect('idDocente', 'Seleccione un docente') && valido;
                valido = validarSelect('idEstatus', 'Seleccione un estatus') && valido;
                break;
        }

        return valido;
    }

    function validarPasoSilencioso(paso) {
        // Versión silenciosa para navegación entre pasos
        switch(paso) {
            case 1:
                return document.querySelector('input[name="nombreActividad"]').value !== '' &&
                       document.querySelector('textarea[name="objetivoActividad"]').value !== '' &&
                       document.querySelector('select[name="idTipoActividad"]').value !== '';
            case 2:
                return document.querySelectorAll('input[name="gruposEtarios[]"]:checked').length > 0 &&
                       document.querySelector('select[name="idGrupoEtnio"]').value !== '';
            case 3:
                return document.querySelector('input[name="fechainicioActividad"]').value !== '' &&
                       document.querySelector('input[name="fechafinActividad"]').value !== '';
            case 4:
                return document.querySelector('select[name="idLugarActividad"]').value !== '' &&
                       document.querySelector('input[name="idHorario"]').value !== '';
            case 5:
                return document.querySelector('select[name="idEmpleado"]').value !== '' &&
                       document.querySelector('select[name="idDocente"]').value !== '';
            default:
                return true;
        }
    }

    function validarCampo(name, mensaje) {
        // Buscar en input, textarea o select
        const campo = document.querySelector('input[name="' + name + '"], textarea[name="' + name + '"], select[name="' + name + '"]');
        if (!campo || !campo.value.trim()) {
            mostrarError(name, mensaje);
            if (campo) campo.classList.add('is-invalid');
            return false;
        }
        return true;
    }

    function validarSelect(name, mensaje) {
        const campo = document.querySelector('select[name="' + name + '"]');
        if (!campo || !campo.value) {
            mostrarError(name, mensaje);
            if (campo) campo.classList.add('is-invalid');
            return false;
        }
        return true;
    }

    function mostrarError(name, mensaje) {
        const errorDiv = document.getElementById('error-' + name);
        if (errorDiv) {
            errorDiv.textContent = mensaje;
            errorDiv.style.display = 'block';
        }
    }

    function limpiarErrores() {
        document.querySelectorAll('.invalid-feedback').forEach(function(el) {
            el.style.display = 'none';
            el.textContent = '';
        });
        document.querySelectorAll('.is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
    }

    // ===== CALENDARIO INTERACTIVO =====
    function inicializarCalendario() {
        renderizarCalendario();

        document.getElementById('cal-prev').addEventListener('click', function() {
            calendarioMesActual--;
            if (calendarioMesActual < 0) {
                calendarioMesActual = 11;
                calendarioAnioActual--;
            }
            renderizarCalendario();
        });

        document.getElementById('cal-next').addEventListener('click', function() {
            calendarioMesActual++;
            if (calendarioMesActual > 11) {
                calendarioMesActual = 0;
                calendarioAnioActual++;
            }
            renderizarCalendario();
        });

        // Inputs de fecha sincronizados con calendario
        document.getElementById('fechaInicio').addEventListener('change', function() {
            fechaInicioSeleccionada = new Date(this.value + 'T00:00:00');
            renderizarCalendario();
            verificarConflictosFecha();
        });

        document.getElementById('fechaFin').addEventListener('change', function() {
            fechaFinSeleccionada = new Date(this.value + 'T00:00:00');
            renderizarCalendario();
            verificarConflictosFecha();
        });
    }

    function renderizarCalendario() {
        const container = document.getElementById('cal-days');
        const monthYearLabel = document.getElementById('cal-month-year');

        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        monthYearLabel.textContent = meses[calendarioMesActual] + ' ' + calendarioAnioActual;

        const primerDia = new Date(calendarioAnioActual, calendarioMesActual, 1).getDay();
        const diasEnMes = new Date(calendarioAnioActual, calendarioMesActual + 1, 0).getDate();
        const hoy = new Date();

        let html = '';

        // Espacios vacíos antes del primer día
        for (let i = 0; i < primerDia; i++) {
            html += '<div class="calendar-day empty"></div>';
        }

        // Días del mes
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const fechaActual = new Date(calendarioAnioActual, calendarioMesActual, dia);
            const fechaStr = formatearFechaISO(fechaActual);

            let clases = ['calendar-day'];

            // Hoy
            if (fechaActual.toDateString() === hoy.toDateString()) {
                clases.push('today');
            }

            // Fecha ocupada (rojo)
            if (esFechaOcupada(fechaActual)) {
                clases.push('occupied');
            }

            // Fecha seleccionada como inicio o fin
            if (fechaInicioSeleccionada && fechaActual.toDateString() === fechaInicioSeleccionada.toDateString()) {
                clases.push('selected');
            }
            if (fechaFinSeleccionada && fechaActual.toDateString() === fechaFinSeleccionada.toDateString()) {
                clases.push('selected');
            }

            // Rango entre inicio y fin
            if (fechaInicioSeleccionada && fechaFinSeleccionada &&
                fechaActual > fechaInicioSeleccionada && fechaActual < fechaFinSeleccionada) {
                clases.push('in-range');
            }

            const claseStr = clases.join(' ');
            const disabled = esFechaOcupada(fechaActual) ? 'disabled' : '';

            html += '<div class="' + claseStr + '" data-fecha="' + fechaStr + '" ' + disabled + '>' + 
                    dia + '</div>';
        }

        container.innerHTML = html;

        // Eventos de click en días
        container.querySelectorAll('.calendar-day:not(.empty):not(.occupied)').forEach(function(dia) {
            dia.addEventListener('click', function() {
                const fechaStr = this.dataset.fecha;
                seleccionarFechaCalendario(fechaStr);
            });
        });
    }

    function esFechaOcupada(fecha) {
        if (!window.fechasOcupadas || !Array.isArray(window.fechasOcupadas)) return false;

        const fechaCheck = new Date(fecha);
        fechaCheck.setHours(0, 0, 0, 0);

        return window.fechasOcupadas.some(function(rango) {
            const inicio = new Date(rango.inicio);
            const fin = new Date(rango.fin);
            inicio.setHours(0, 0, 0, 0);
            fin.setHours(0, 0, 0, 0);
            return fechaCheck >= inicio && fechaCheck <= fin;
        });
    }

    function seleccionarFechaCalendario(fechaStr) {
        const fecha = new Date(fechaStr + 'T00:00:00');

        if (!fechaInicioSeleccionada || (fechaInicioSeleccionada && fechaFinSeleccionada)) {
            // Nueva selección
            fechaInicioSeleccionada = fecha;
            fechaFinSeleccionada = null;
            document.getElementById('fechaInicio').value = fechaStr;
            document.getElementById('fechaFin').value = '';
        } else if (fechaInicioSeleccionada && !fechaFinSeleccionada) {
            // Seleccionar fin
            if (fecha < fechaInicioSeleccionada) {
                // Si selecciona una fecha anterior, intercambiar
                fechaFinSeleccionada = fechaInicioSeleccionada;
                fechaInicioSeleccionada = fecha;
                document.getElementById('fechaInicio').value = fechaStr;
                document.getElementById('fechaFin').value = formatearFechaISO(fechaFinSeleccionada);
            } else {
                fechaFinSeleccionada = fecha;
                document.getElementById('fechaFin').value = fechaStr;
            }
        }

        renderizarCalendario();
        verificarConflictosFecha();
    }

    function formatearFechaISO(fecha) {
        const y = fecha.getFullYear();
        const m = String(fecha.getMonth() + 1).padStart(2, '0');
        const d = String(fecha.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function verificarConflictosFecha() {
        // Verificar si las fechas seleccionadas tienen conflictos
        const fechaIni = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;

        if (fechaIni && fechaFin) {
            const inicio = new Date(fechaIni);
            const fin = new Date(fechaFin);
            let conflicto = false;

            for (let d = new Date(inicio); d <= fin; d.setDate(d.getDate() + 1)) {
                if (esFechaOcupada(new Date(d))) {
                    conflicto = true;
                    break;
                }
            }

            if (conflicto) {
                mostrarAlerta('warning', 'Advertencia: El rango de fechas seleccionado incluye días ocupados.');
            }
        }
    }

    // ===== GENERAR FECHAS DE SESIONES =====
    function inicializarEventos() {
        const btnGenerar = document.getElementById('btnGenerarFechas');
        if (btnGenerar) {
            btnGenerar.addEventListener('click', generarFechasSesiones);
        }

        // Lugar -> mostrar/ocultar espacio
        const selectLugar = document.getElementById('selectLugar');
        if (selectLugar) {
            selectLugar.addEventListener('change', function() {
                const opcion = this.options[this.selectedIndex];
                const esSede = opcion.dataset.sede === '1';
                const container = document.getElementById('espacio-container');

                if (esSede) {
                    container.style.display = 'block';
                    cargarHorariosDisponibles();
                } else {
                    container.style.display = 'none';
                    document.getElementById('selectEspacio').value = '';
                    cargarHorariosDisponibles();
                }
            });
        }

        // Espacio -> cargar horarios
        const selectEspacio = document.getElementById('selectEspacio');
        if (selectEspacio) {
            selectEspacio.addEventListener('change', cargarHorariosDisponibles);
        }

        // Fechas -> cargar horarios
        document.getElementById('fechaInicio').addEventListener('change', cargarHorariosDisponibles);
        document.getElementById('fechaFin').addEventListener('change', cargarHorariosDisponibles);
    }

    function generarFechasSesiones() {
        const cantSesiones = parseInt(document.querySelector('input[name="cantSesionesPlanificada"]').value) || 1;
        const fechaIni = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;

        if (!fechaIni || !fechaFin) {
            mostrarAlerta('danger', 'Seleccione primero las fechas de inicio y fin.');
            return;
        }

        const inicio = new Date(fechaIni);
        const fin = new Date(fechaFin);
        const diasTotales = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;

        if (cantSesiones > diasTotales) {
            mostrarAlerta('danger', 'La cantidad de sesiones no puede ser mayor que los días disponibles.');
            return;
        }

        fechasSesionesGeneradas = [];
        const intervalo = Math.floor(diasTotales / cantSesiones);

        for (let i = 0; i < cantSesiones; i++) {
            const fechaSesion = new Date(inicio);
            fechaSesion.setDate(inicio.getDate() + (i * intervalo));

            // Si cae en fecha ocupada, buscar el siguiente día libre
            while (esFechaOcupada(fechaSesion) && fechaSesion <= fin) {
                fechaSesion.setDate(fechaSesion.getDate() + 1);
            }

            if (fechaSesion <= fin) {
                fechasSesionesGeneradas.push(formatearFechaISO(fechaSesion));
            }
        }

        mostrarSesionesGeneradas();
        mostrarAlerta('success', 'Se generaron ' + fechasSesionesGeneradas.length + ' fechas de sesiones.');
    }

    function mostrarSesionesGeneradas() {
        const container = document.getElementById('sesiones-container');
        const list = document.getElementById('sesiones-list');

        if (fechasSesionesGeneradas.length === 0) {
            container.style.display = 'none';
            return;
        }

        let html = '';
        fechasSesionesGeneradas.forEach(function(fecha, index) {
            const fechaObj = new Date(fecha + 'T00:00:00');
            const fechaFormateada = fechaObj.toLocaleDateString('es-VE', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
            html += '<span class="sesion-badge">' +
                    '<i class="bi bi-calendar-check"></i>' +
                    'Sesión ' + (index + 1) + ': ' + fechaFormateada +
                    '</span>';
        });

        list.innerHTML = html;
        container.style.display = 'block';
    }

    // ===== HORARIOS DISPONIBLES =====
    function cargarHorariosDisponibles() {
        const fecha = document.getElementById('fechaInicio').value;
        const idLugar = document.getElementById('selectLugar').value;
        const idEspacio = document.getElementById('selectEspacio').value;
        const container = document.getElementById('horarios-container');

        if (!fecha || !idLugar) {
            container.innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Seleccione primero las fechas y el lugar para ver los horarios disponibles.</div>';
            return;
        }

        // Mostrar loading
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div><span class="ms-2 text-muted small">Cargando horarios...</span></div>';

        // Petición AJAX para horarios ocupados
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'index.php?action=obtenerHorariosOcupados&fecha=' + encodeURIComponent(fecha) + 
                  '&idLugar=' + encodeURIComponent(idLugar) + 
                  '&idEspacio=' + encodeURIComponent(idEspacio || ''), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const horariosOcupados = JSON.parse(xhr.responseText);
                    renderizarHorarios(horariosOcupados);
                } catch(e) {
                    renderizarHorarios([]);
                }
            } else {
                renderizarHorarios([]);
            }
        };
        xhr.onerror = function() {
            renderizarHorarios([]);
        };
        xhr.send();
    }

    function renderizarHorarios(horariosOcupados) {
        const container = document.getElementById('horarios-container');
        const horariosSelect = document.getElementById('horariosSelect');

        if (!horariosSelect) {
            container.innerHTML = '<div class="alert alert-warning">No hay horarios configurados.</div>';
            return;
        }

        let html = '';
        const opciones = horariosSelect.querySelectorAll('option');

        opciones.forEach(function(op) {
            if (!op.value) return;

            const estaOcupado = horariosOcupados.some(function(h) {
                return h.idHorario === op.value;
            });

            const claseOcupado = estaOcupado ? 'occupied' : '';
            const disabled = estaOcupado ? 'disabled' : '';
            const icono = estaOcupado ? 'bi-x-circle' : 'bi-clock';
            const titulo = estaOcupado ? 'Horario ocupado' : 'Horario disponible';

            html += '<label class="horario-chip ' + claseOcupado + '" title="' + titulo + '">' +
                    '<input type="radio" name="idHorario_radio" value="' + op.value + '" ' + disabled + '>' +
                    '<span class="horario-content">' +
                    '<i class="bi ' + icono + '"></i>' + op.textContent +
                    '</span>' +
                    '</label>';
        });

        if (html === '') {
            container.innerHTML = '<div class="alert alert-warning">No hay horarios configurados.</div>';
        } else {
            container.innerHTML = html;

            // Evento para sincronizar con input hidden
            container.querySelectorAll('input[name="idHorario_radio"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.getElementById('idHorarioInput').value = this.value;
                    // Quitar selección visual de otros
                    container.querySelectorAll('.horario-chip').forEach(function(chip) {
                        chip.classList.remove('selected');
                    });
                    this.closest('.horario-chip').classList.add('selected');
                });
            });
        }
    }

    // ===== RESUMEN LATERAL (CARRITO) =====
    function inicializarResumenLateral() {
        // Escuchar cambios en todos los campos del formulario
        const form = document.getElementById('formActividad');
        if (form) {
            form.addEventListener('change', actualizarResumenLateral);
            form.addEventListener('input', debounce(actualizarResumenLateral, 300));
        }
    }

    function actualizarResumenLateral() {
        const carritoVacio = document.querySelector('.carrito-vacio');
        let progreso = 0;
        const pasoPorcentaje = 100 / totalPasos;

        // Paso 1: Información Básica
        const p1 = document.getElementById('carrito-paso1');
        const p1Body = document.getElementById('carrito-body-paso1');
        const nombre = document.querySelector('input[name="nombreActividad"]')?.value;
        const tipo = document.querySelector('select[name="idTipoActividad"]')?.selectedOptions[0]?.text;
        const vertice = document.querySelector('select[name="idVertice"]')?.selectedOptions[0]?.text;
        const area = document.querySelector('select[name="idAreaE"]')?.selectedOptions[0]?.text;

        if (nombre || tipo) {
            p1.style.display = 'block';
            carritoVacio.style.display = 'none';
            p1Body.innerHTML = 
                '<div class="carrito-item"><span class="carrito-label">Nombre</span><span class="carrito-value">' + (nombre || '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Tipo</span><span class="carrito-value">' + (tipo !== 'Seleccione...' ? tipo : '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Vértice</span><span class="carrito-value">' + (vertice !== 'Seleccione...' ? vertice : '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Área</span><span class="carrito-value">' + (area !== 'Seleccione...' ? area : '-') + '</span></div>';
            progreso += pasoPorcentaje;
        } else {
            p1.style.display = 'none';
        }

        // Paso 2: Grupos y Capacidad
        const p2 = document.getElementById('carrito-paso2');
        const p2Body = document.getElementById('carrito-body-paso2');
        const gruposCheck = document.querySelectorAll('input[name="gruposEtarios[]"]:checked');
        const grupoEtnio = document.querySelector('select[name="idGrupoEtnio"]')?.selectedOptions[0]?.text;
        const unidad = document.querySelector('select[name="idUnidadMedida"]')?.selectedOptions[0]?.text;
        const cantPerso = document.querySelector('input[name="cantPersoAtender"]')?.value;

        if (gruposCheck.length > 0 || cantPerso) {
            p2.style.display = 'block';
            carritoVacio.style.display = 'none';
            let gruposHtml = '';
            gruposCheck.forEach(function(g) {
                const label = g.closest('.grupo-etario-chip').querySelector('.chip-nombre').textContent;
                gruposHtml += '<span class="badge bg-info me-1">' + label + '</span>';
            });
            p2Body.innerHTML = 
                '<div class="carrito-item"><span class="carrito-label">Grupos Etarios</span><span class="carrito-value badge-group">' + (gruposHtml || '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Grupo Étnico</span><span class="carrito-value">' + (grupoEtnio !== 'Seleccione...' ? grupoEtnio : '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Unidad</span><span class="carrito-value">' + (unidad !== 'Seleccione...' ? unidad : '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Personas</span><span class="carrito-value">' + (cantPerso || '-') + '</span></div>';
            progreso += pasoPorcentaje;
        } else {
            p2.style.display = 'none';
        }

        // Paso 3: Fechas y Sesiones
        const p3 = document.getElementById('carrito-paso3');
        const p3Body = document.getElementById('carrito-body-paso3');
        const fechaIni = document.getElementById('fechaInicio')?.value;
        const fechaFin = document.getElementById('fechaFin')?.value;
        const cantSes = document.querySelector('input[name="cantSesionesPlanificada"]')?.value;

        if (fechaIni || fechaFin) {
            p3.style.display = 'block';
            carritoVacio.style.display = 'none';
            let sesionesHtml = '';
            if (fechasSesionesGeneradas.length > 0) {
                sesionesHtml = '<span class="badge bg-success">' + fechasSesionesGeneradas.length + ' sesiones generadas</span>';
            }
            p3Body.innerHTML = 
                '<div class="carrito-item"><span class="carrito-label">Inicio</span><span class="carrito-value">' + (fechaIni ? formatearFechaVenezuela(fechaIni) : '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Fin</span><span class="carrito-value">' + (fechaFin ? formatearFechaVenezuela(fechaFin) : '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Sesiones</span><span class="carrito-value">' + (cantSes || '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Generadas</span><span class="carrito-value badge-group">' + (sesionesHtml || '-') + '</span></div>';
            progreso += pasoPorcentaje;
        } else {
            p3.style.display = 'none';
        }

        // Paso 4: Lugar y Espacio
        const p4 = document.getElementById('carrito-paso4');
        const p4Body = document.getElementById('carrito-body-paso4');
        const lugar = document.querySelector('select[name="idLugarActividad"]')?.selectedOptions[0]?.text;
        const espacio = document.querySelector('select[name="idEspacioUtilizar"]')?.selectedOptions[0]?.text;
        const horario = document.querySelector('input[name="idHorario_radio"]:checked');
        const horarioText = horario ? horario.closest('.horario-chip').querySelector('.horario-content').textContent.trim() : '';

        if (lugar && lugar !== 'Seleccione un lugar...') {
            p4.style.display = 'block';
            carritoVacio.style.display = 'none';
            p4Body.innerHTML = 
                '<div class="carrito-item"><span class="carrito-label">Lugar</span><span class="carrito-value">' + lugar.replace('(Sede)', '').trim() + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Espacio</span><span class="carrito-value">' + (espacio !== 'Seleccione un espacio...' ? espacio : 'No aplica') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Horario</span><span class="carrito-value">' + (horarioText || '-') + '</span></div>';
            progreso += pasoPorcentaje;
        } else {
            p4.style.display = 'none';
        }

        // Paso 5: Responsables
        const p5 = document.getElementById('carrito-paso5');
        const p5Body = document.getElementById('carrito-body-paso5');
        const empleado = document.querySelector('select[name="idEmpleado"]')?.selectedOptions[0]?.text;
        const docente = document.querySelector('select[name="idDocente"]')?.selectedOptions[0]?.text;
        const estatus = document.querySelector('select[name="idEstatus"]')?.selectedOptions[0]?.text;

        if (empleado && empleado !== 'Seleccione...') {
            p5.style.display = 'block';
            carritoVacio.style.display = 'none';
            p5Body.innerHTML = 
                '<div class="carrito-item"><span class="carrito-label">Empleado</span><span class="carrito-value">' + (empleado !== 'Seleccione...' ? empleado.split('(')[0].trim() : '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Docente</span><span class="carrito-value">' + (docente !== 'Seleccione...' ? docente.split('(')[0].trim() : '-') + '</span></div>' +
                '<div class="carrito-item"><span class="carrito-label">Estatus</span><span class="carrito-value">' + (estatus !== 'Seleccione...' ? estatus : '-') + '</span></div>';
            progreso += pasoPorcentaje;
        } else {
            p5.style.display = 'none';
        }

        // Actualizar progreso
        if (progreso === 0) {
            carritoVacio.style.display = 'block';
        }
        document.getElementById('carrito-progreso').textContent = Math.round(progreso) + '%';
        document.getElementById('carrito-progress-bar').style.width = progreso + '%';
    }

    function generarResumenFinal() {
        const container = document.getElementById('resumen-content');
        const formData = new FormData(document.getElementById('formActividad'));

        // Obtener textos de selects
        const getSelectText = function(name) {
            const sel = document.querySelector('select[name="' + name + '"]');
            return sel && sel.selectedIndex > 0 ? sel.options[sel.selectedIndex].text : 'No seleccionado';
        };

        const getInputValue = function(name) {
            const input = document.querySelector('[name="' + name + '"]');
            return input ? input.value : '';
        };

        const gruposCheck = document.querySelectorAll('input[name="gruposEtarios[]"]:checked');
        let gruposText = '';
        gruposCheck.forEach(function(g) {
            gruposText += '<span class="badge bg-info me-1 mb-1">' + g.closest('.grupo-etario-chip').querySelector('.chip-nombre').textContent + '</span>';
        });

        const horarioRadio = document.querySelector('input[name="idHorario_radio"]:checked');
        const horarioText = horarioRadio ? horarioRadio.closest('.horario-chip').querySelector('.horario-content').textContent.trim() : 'No seleccionado';

        let sesionesText = '';
        if (fechasSesionesGeneradas.length > 0) {
            fechasSesionesGeneradas.forEach(function(f, i) {
                const fechaObj = new Date(f + 'T00:00:00');
                sesionesText += '<span class="badge bg-success me-1 mb-1">S' + (i+1) + ': ' + fechaObj.toLocaleDateString('es-VE') + '</span>';
            });
        }

        container.innerHTML = 
            '<div class="resumen-section">' +
            '<div class="resumen-section-title"><i class="bi bi-info-circle"></i> Información Básica</div>' +
            '<div class="resumen-row"><span class="label">Nombre:</span><span class="value">' + getInputValue('nombreActividad') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Objetivo:</span><span class="value">' + getInputValue('objetivoActividad') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Descripción:</span><span class="value">' + getInputValue('descActividad') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Estrategia:</span><span class="value">' + getSelectText('idEstDesarrollo') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Tipo:</span><span class="value">' + getSelectText('idTipoActividad') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Vértice:</span><span class="value">' + getSelectText('idVertice') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Área:</span><span class="value">' + getSelectText('idAreaE') + '</span></div>' +
            '</div>' +
            '<div class="resumen-section">' +
            '<div class="resumen-section-title"><i class="bi bi-people"></i> Grupos y Capacidad</div>' +
            '<div class="resumen-row"><span class="label">Grupos Etarios:</span><span class="value">' + (gruposText || 'Ninguno') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Grupo Étnico:</span><span class="value">' + getSelectText('idGrupoEtnio') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Unidad de Medida:</span><span class="value">' + getSelectText('idUnidadMedida') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Personas a atender:</span><span class="value">' + getInputValue('cantPersoAtender') + '</span></div>' +
            '</div>' +
            '<div class="resumen-section">' +
            '<div class="resumen-section-title"><i class="bi bi-calendar3"></i> Fechas y Sesiones</div>' +
            '<div class="resumen-row"><span class="label">Fecha Inicio:</span><span class="value">' + formatearFechaVenezuela(getInputValue('fechainicioActividad')) + '</span></div>' +
            '<div class="resumen-row"><span class="label">Fecha Fin:</span><span class="value">' + formatearFechaVenezuela(getInputValue('fechafinActividad')) + '</span></div>' +
            '<div class="resumen-row"><span class="label">Sesiones Planificadas:</span><span class="value">' + getInputValue('cantSesionesPlanificada') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Fechas Generadas:</span><span class="value">' + (sesionesText || 'No generadas') + '</span></div>' +
            '</div>' +
            '<div class="resumen-section">' +
            '<div class="resumen-section-title"><i class="bi bi-geo-alt"></i> Lugar y Espacio</div>' +
            '<div class="resumen-row"><span class="label">Lugar:</span><span class="value">' + getSelectText('idLugarActividad') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Espacio:</span><span class="value">' + getSelectText('idEspacioUtilizar') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Horario:</span><span class="value">' + horarioText + '</span></div>' +
            '</div>' +
            '<div class="resumen-section">' +
            '<div class="resumen-section-title"><i class="bi bi-person-badge"></i> Responsables</div>' +
            '<div class="resumen-row"><span class="label">Empleado:</span><span class="value">' + getSelectText('idEmpleado') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Docente:</span><span class="value">' + getSelectText('idDocente') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Estatus:</span><span class="value">' + getSelectText('idEstatus') + '</span></div>' +
            '<div class="resumen-row"><span class="label">Tipo Entrega:</span><span class="value">' + getSelectText('idTipEntrega') + '</span></div>' +
            '</div>';
    }

    function formatearFechaVenezuela(fechaStr) {
        if (!fechaStr) return '-';
        const fecha = new Date(fechaStr + 'T00:00:00');
        return fecha.toLocaleDateString('es-VE', { day: '2-digit', month: 'long', year: 'numeric' });
    }

    // ===== GUARDAR ACTIVIDAD =====
    function guardarActividad() {
        const form = document.getElementById('formActividad');
        const formData = new FormData(form);

        // Agregar fechas de sesiones generadas
        if (fechasSesionesGeneradas.length > 0) {
            fechasSesionesGeneradas.forEach(function(fecha) {
                formData.append('fechasSesiones[]', fecha);
            });
        }

        const btnGuardar = document.getElementById('btnGuardar');
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?action=guardarActividad', true);
        xhr.onload = function() {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar Actividad';

            if (xhr.status === 200) {
                try {
                    const respuesta = JSON.parse(xhr.responseText);
                    if (respuesta.status === 'success') {
                        mostrarAlerta('success', respuesta.message);
                        setTimeout(function() {
                            window.location.href = 'index.php?action=actividades';
                        }, 1500);
                    } else {
                        mostrarAlerta('danger', respuesta.message || 'Error al guardar');
                    }
                } catch(e) {
                    mostrarAlerta('danger', 'Error en la respuesta del servidor');
                }
            } else {
                mostrarAlerta('danger', 'Error de conexión');
            }
        };
        xhr.onerror = function() {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar Actividad';
            mostrarAlerta('danger', 'Error de conexión');
        };
        xhr.send(formData);
    }

    // ===== ALERTAS =====
    function mostrarAlerta(tipo, mensaje) {
        const alerta = document.getElementById('registro-alerta');
        const icono = document.getElementById('alerta-icono');
        const texto = document.getElementById('alerta-texto');

        alerta.className = 'alert alert-' + tipo + ' shadow-sm';
        alerta.style.display = 'block';

        const iconos = {
            success: 'bi-check-circle-fill',
            danger: 'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };

        icono.className = 'bi ' + (iconos[tipo] || 'bi-info-circle') + ' me-2';
        texto.textContent = mensaje;

        setTimeout(function() {
            alerta.style.display = 'none';
        }, 5000);
    }

    // ===== UTILIDADES =====
    function debounce(func, wait) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                func.apply(context, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

})();