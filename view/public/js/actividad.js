// Registro y edición de actividades
// Cada sesión tiene: fecha (paso 3), horario y lugar (paso 4) y docentes (paso 5).
// El sistema pre-marca los docentes disponibles por fecha+horario y avisa los ocupados
// sin bloquearlos: se puede asignar cualquier combinación (todos en todas, uno por sesión,
// todos en una sola, sesiones simultáneas en distintos lugares).


(function () {
    'use strict';

    const totalPasos = 6;
    let pasoActual = 1;
    let fechasSesiones = [];
    let horariosSesiones = [];
    let lugaresSesiones = [];
    let docentesSesiones = [];
    let sesionesDocentesTocadas = new Set();
    let ocupadosPorSesion = {};
    let conservarSesionesIniciales = Boolean(window.modoEdicion);
    let capacidadMinima = 0;
    let guardando = false;
    let consultaDisponibilidad = 0;
    let consultaOcupacion = 0;
    let timerDisponibilidad = null;
    let timerSesiones = null;
    let timerOcupacion = null;

    document.addEventListener('DOMContentLoaded', function () {
        inicializarWizard();
        inicializarFechas();
        inicializarSelectores();
        inicializarSelecciones();
        inicializarSesionesManuales();
        inicializarResumen();
        inicializarModales();

        if (conservarSesionesIniciales && Array.isArray(window.fechasSesionesIniciales)) {
            fechasSesiones = window.fechasSesionesIniciales.slice();
            horariosSesiones = fechasSesiones.map(function () { return ''; });
            lugaresSesiones = fechasSesiones.map(function () { return ''; });
            docentesSesiones = fechasSesiones.map(function () { return []; });
            if (Array.isArray(window.sesionesIniciales)) {
                window.sesionesIniciales.forEach(function (sesion, indice) {
                    if (indice >= fechasSesiones.length) return;
                    if (sesion.idHorario) horariosSesiones[indice] = sesion.idHorario;
                    if (sesion.idLugar) lugaresSesiones[indice] = sesion.idLugar;
                    if (Array.isArray(sesion.docentes)) {
                        docentesSesiones[indice] = sesion.docentes.slice();
                        sesionesDocentesTocadas.add(indice);
                    }
                });
            }
            mostrarSesiones();
            mostrarPlanificacionSesiones();
            actualizarInfoRango();
        } else {
            generarSesiones();
        }

        actualizarEspacios();
        actualizarResumenSelectores();
        consultarDisponibilidad();
        programarOcupacion();
        actualizarResumen();
    });

    function obtenerSeleccionados(nombre) {
        return Array.from(document.querySelectorAll('input[name="' + nombre + '"]:checked')).map(function (input) {
            return input.value;
        });
    }

    function obtenerTextos(nombre) {
        return Array.from(document.querySelectorAll('input[name="' + nombre + '"]:checked')).map(function (input) {
            if (input.dataset.nombre) return input.dataset.nombre;
            const etiqueta = input.closest('label');
            return etiqueta ? etiqueta.textContent.trim().replace(/\s+/g, ' ') : input.value;
        });
    }

    function escapar(valor) {
        const div = document.createElement('div');
        div.textContent = valor === null || valor === undefined ? '' : String(valor);
        return div.innerHTML;
    }

    function textoSelect(nombre) {
        const select = document.querySelector('select[name="' + nombre + '"]');
        return select && select.value ? select.options[select.selectedIndex].textContent.trim() : 'No seleccionado';
    }

    function valorCampo(nombre) {
        const campo = document.querySelector('[name="' + nombre + '"]');
        return campo ? campo.value.trim() : '';
    }

    function obtenerFechaMinima() {
        const fecha = new Date();
        fecha.setHours(0, 0, 0, 0);
        fecha.setDate(fecha.getDate() - 15);
        const anio = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return anio + '-' + mes + '-' + dia;
    }

    function formatearFecha(fecha) {
        if (!fecha) return '-';
        return new Date(fecha + 'T00:00:00').toLocaleDateString('es-VE', {
            day: '2-digit', month: 'short', year: 'numeric'
        });
    }

    function debounce(funcion, espera) {
        let timer;
        return function () {
            const args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                funcion.apply(null, args);
            }, espera);
        };
    }

    // Convierte "08:00 AM - 10:00 AM" a minutos [inicio, fin]
    function intervaloHorario(texto) {
        const coincidencia = /(\d{1,2}):(\d{2})\s*(AM|PM)\s*-\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i.exec(String(texto || ''));
        if (!coincidencia) return null;
        const aMinutos = function (hora, minutos, periodo) {
            let valor = Number(hora) % 12;
            if (periodo.toUpperCase() === 'PM') valor += 12;
            return valor * 60 + Number(minutos);
        };
        return [aMinutos(coincidencia[1], coincidencia[2], coincidencia[3]), aMinutos(coincidencia[4], coincidencia[5], coincidencia[6])];
    }

    function horariosSeSolapan(primero, segundo) {
        const a = intervaloHorario(primero);
        const b = intervaloHorario(segundo);
        return !!(a && b && a[0] < b[1] && b[0] < a[1]);
    }

    // Reglas de planificación de sesiones. Devuelve {paso, mensaje} del primer problema o null.
    // 1. Máximo 3 sesiones el mismo día.
    // 2. El mismo lugar no se repite el mismo día en el mismo horario (ni solapado).
    // 3. Toda sesión debe tener al menos un docente asignado.
    // 4. Un docente ocupado por otra actividad en esa fecha y horario no puede asignarse.
    function validarReglasSesiones() {
        const porFecha = {};
        fechasSesiones.forEach(function (fecha) {
            porFecha[fecha] = (porFecha[fecha] || 0) + 1;
        });
        const fechaExcedida = Object.keys(porFecha).find(function (fecha) { return porFecha[fecha] > 3; });
        if (fechaExcedida) {
            return { paso: 3, mensaje: 'El día ' + formatearFecha(fechaExcedida) + ' tiene ' + porFecha[fechaExcedida] + ' sesiones. Solo se permiten máximo 3 sesiones por día.' };
        }

        for (let i = 0; i < fechasSesiones.length; i++) {
            for (let j = i + 1; j < fechasSesiones.length; j++) {
                if (fechasSesiones[i] !== fechasSesiones[j]) continue;
                if (!lugaresSesiones[i] || lugaresSesiones[i] !== lugaresSesiones[j]) continue;
                if (horariosSesiones[i] === horariosSesiones[j] ||
                    horariosSeSolapan(nombreHorario(horariosSesiones[i]), nombreHorario(horariosSesiones[j]))) {
                    return { paso: 4, mensaje: 'Las sesiones ' + (i + 1) + ' y ' + (j + 1) + ' usan el mismo lugar (' + nombreLugar(lugaresSesiones[i]) + ') el ' + formatearFecha(fechasSesiones[i]) + ' en horarios que se solapan. En distinto horario sí es posible.' };
                }
            }
        }

        for (let i = 0; i < fechasSesiones.length; i++) {
            const asignados = Array.isArray(docentesSesiones[i]) ? docentesSesiones[i] : [];
            if (!asignados.length) {
                return { paso: 5, mensaje: 'La sesión ' + (i + 1) + ' (' + formatearFecha(fechasSesiones[i]) + ') no tiene docente asignado. Cada sesión debe tener al menos un docente; si todos están ocupados en ese horario, cambie la fecha u horario de la sesión.' };
            }
            const ocupados = ocupadosPorSesion[i] || [];
            const ocupado = asignados.find(function (id) { return ocupados.indexOf(id) !== -1; });
            if (ocupado) {
                return { paso: 5, mensaje: 'El docente ' + nombreDocente(ocupado) + ' ya tiene otra actividad el ' + formatearFecha(fechasSesiones[i]) + ' en ese horario, por lo que no puede asignarse a la sesión ' + (i + 1) + '.' };
            }
        }

        return null;
    }

    // Asistente
    function inicializarWizard() {
        document.getElementById('btnSiguiente').addEventListener('click', function () {
            if (validarPaso(pasoActual) && pasoActual < totalPasos) irAPaso(pasoActual + 1);
        });

        document.getElementById('btnAnterior').addEventListener('click', function () {
            if (pasoActual > 1) irAPaso(pasoActual - 1);
        });

        document.getElementById('formActividad').addEventListener('submit', function (event) {
            event.preventDefault();
            guardarActividad();
        });

        document.querySelectorAll('.wizard-step').forEach(function (paso) {
            paso.addEventListener('click', function () {
                const destino = Number(this.dataset.step);
                if (destino < pasoActual || puedeNavegarA(destino)) irAPaso(destino);
            });
        });
    }

    function irAPaso(paso) {
        document.getElementById('step-' + pasoActual).classList.remove('active');
        document.querySelector('.wizard-step[data-step="' + pasoActual + '"]').classList.remove('active');

        for (let i = 1; i <= totalPasos; i++) {
            const item = document.querySelector('.wizard-step[data-step="' + i + '"]');
            item.classList.toggle('completed', i < paso);
        }

        pasoActual = paso;
        document.getElementById('step-' + pasoActual).classList.add('active');
        document.querySelector('.wizard-step[data-step="' + pasoActual + '"]').classList.add('active');
        document.getElementById('paso-indicador').textContent = 'Paso ' + pasoActual + ' de ' + totalPasos;
        document.getElementById('wizard-progress-bar').style.width = ((pasoActual / totalPasos) * 100) + '%';
        document.getElementById('btnAnterior').disabled = pasoActual === 1;
        document.getElementById('btnSiguiente').style.display = pasoActual === totalPasos ? 'none' : 'inline-block';
        document.getElementById('btnGuardar').style.display = pasoActual === totalPasos ? 'inline-block' : 'none';

        if (pasoActual === 4) mostrarPlanificacionSesiones();
        if (pasoActual === 5) {
            mostrarDocentesSesiones();
            programarOcupacion();
        }
        if (pasoActual === totalPasos) generarResumenFinal();
        actualizarResumen();
    }

    function puedeNavegarA(destino) {
        for (let i = 1; i < destino; i++) {
            if (!validarPasoSilencioso(i)) return false;
        }
        return true;
    }

    function validarCampo(nombre, mensaje) {
        const campo = document.querySelector('[name="' + nombre + '"]');
        if (!campo || !campo.value.trim()) {
            mostrarError(nombre, mensaje);
            if (campo) campo.classList.add('is-invalid');
            return false;
        }
        return true;
    }

    function validarMinimo(nombre, minimo, mensaje) {
        const campo = document.querySelector('[name="' + nombre + '"]');
        if (campo && campo.value.trim() && campo.value.trim().length < minimo) {
            mostrarError(nombre, mensaje);
            campo.classList.add('is-invalid');
            return false;
        }
        return true;
    }

    function validarNumero(nombre, minimo, maximo, mensaje) {
        const numero = Number(valorCampo(nombre));
        if (!Number.isInteger(numero) || numero < minimo || numero > maximo) {
            mostrarError(nombre, mensaje);
            const campo = document.querySelector('[name="' + nombre + '"]');
            if (campo) campo.classList.add('is-invalid');
            return false;
        }
        return true;
    }

    function validarGrupo(nombre, error, mensaje) {
        if (obtenerSeleccionados(nombre).length === 0) {
            mostrarError(error, mensaje);
            return false;
        }
        return true;
    }

    function sesionesDentroDeRango() {
        const inicio = valorCampo('fechainicioActividad');
        const fin = valorCampo('fechafinActividad');
        if (!inicio || !fin) return false;
        return fechasSesiones.every(function (fecha) {
            return fecha && fecha >= inicio && fecha <= fin;
        });
    }

    function horariosSesionesCompletos() {
        return fechasSesiones.length > 0 && fechasSesiones.every(function (fecha, indice) {
            return horariosSesiones[indice] && horariosSesiones[indice] !== '';
        });
    }

    function validarPaso(paso) {
        let valido = true;
        limpiarErrores();

        if (paso === 1) {
            ['nombreActividad', 'objetivoActividad', 'descActividad', 'idEstDesarrollo', 'idTipoActividad', 'idVertice', 'idAreaE'].forEach(function (nombre) {
                valido = validarCampo(nombre, 'Este campo es obligatorio') && valido;
            });
            valido = validarMinimo('nombreActividad', 3, 'El nombre debe tener al menos 3 caracteres') && valido;
            valido = validarMinimo('objetivoActividad', 10, 'El objetivo debe tener al menos 10 caracteres') && valido;
            valido = validarMinimo('descActividad', 10, 'La descripción debe tener al menos 10 caracteres') && valido;
        } else if (paso === 2) {
            valido = validarGrupo('gruposEtarios[]', 'gruposEtarios', 'Seleccione al menos un grupo etario') && valido;
            valido = validarCampo('idGrupoEtnio', 'Seleccione un grupo étnico') && valido;
            valido = validarCampo('idUnidadMedida', 'Seleccione una unidad de medida') && valido;
        } else if (paso === 3) {
            valido = validarCampo('fechainicioActividad', 'Seleccione la fecha de inicio') && valido;
            valido = validarCampo('fechafinActividad', 'Seleccione la fecha de fin') && valido;
            valido = validarNumero('cantSesionesPlanificada', 1, 100, 'Ingrese una cantidad entre 1 y 100') && valido;
            if (valorCampo('fechainicioActividad') && valorCampo('fechainicioActividad') < obtenerFechaMinima()) {
                mostrarError('fechainicioActividad', 'Solo se permiten hasta 15 días antes de la fecha actual (' + formatearFecha(obtenerFechaMinima()) + ' en adelante)');
                valido = false;
            }
            if (valorCampo('fechainicioActividad') && valorCampo('fechafinActividad') < valorCampo('fechainicioActividad')) {
                mostrarError('fechafinActividad', 'La fecha de fin no puede ser anterior a la de inicio');
                valido = false;
            }
            const cantidad = Number(valorCampo('cantSesionesPlanificada'));
            if (fechasSesiones.length !== cantidad) {
                mostrarError('cantSesionesPlanificada', 'Debe haber una fecha marcada para cada sesión planificada');
                valido = false;
            }
            if (!sesionesDentroDeRango()) {
                mostrarError('cantSesionesPlanificada', 'Hay sesiones con fechas vacías o fuera del rango de la actividad');
                valido = false;
            }
            const reglaFechas = validarReglasSesiones();
            if (reglaFechas && reglaFechas.paso === 3) {
                mostrarError('cantSesionesPlanificada', reglaFechas.mensaje);
                valido = false;
            }
        } else if (paso === 4) {
            valido = validarGrupo('idLugarActividad[]', 'idLugarActividad', 'Seleccione al menos un lugar') && valido;
            valido = validarEspacios() && valido;
            valido = validarNumero('cantPersoAtender', 1, 99999, 'Ingrese una cantidad de personas válida') && valido;
            if (capacidadMinima > 0 && Number(valorCampo('cantPersoAtender')) > capacidadMinima) {
                mostrarError('cantPersoAtender', 'La cantidad excede la capacidad disponible (' + capacidadMinima + ')');
                valido = false;
            }
            valido = validarGrupo('idHorario[]', 'idHorario', 'Seleccione al menos un horario disponible') && valido;
            if (valido && !horariosSesionesCompletos()) {
                mostrarError('sesionesHorario', 'Asigne un horario a cada sesión');
                document.getElementById('error-sesionesHorario').style.display = 'block';
                valido = false;
            }
            const reglaLugares = validarReglasSesiones();
            if (reglaLugares && reglaLugares.paso === 4) {
                mostrarError('sesionesHorario', reglaLugares.mensaje);
                document.getElementById('error-sesionesHorario').style.display = 'block';
                valido = false;
            }
        } else if (paso === 5) {
            valido = validarGrupo('idEmpleado[]', 'idEmpleado', 'Seleccione al menos un responsable') && valido;
            valido = validarGrupo('idDocente[]', 'idDocente', 'Seleccione al menos un docente') && valido;
            const reglaDocentes = validarReglasSesiones();
            if (reglaDocentes && reglaDocentes.paso === 5) {
                mostrarError('docentesSesiones', reglaDocentes.mensaje);
                valido = false;
            }
        }

        return valido;
    }

    function validarPasoSilencioso(paso) {
        if (paso === 1) {
            return valorCampo('nombreActividad').length >= 3 && valorCampo('objetivoActividad').length >= 10 &&
                valorCampo('descActividad').length >= 10 && ['idEstDesarrollo', 'idTipoActividad', 'idVertice', 'idAreaE'].every(function (nombre) {
                    return valorCampo(nombre) !== '';
                });
        }
        if (paso === 2) {
            return obtenerSeleccionados('gruposEtarios[]').length > 0 && valorCampo('idGrupoEtnio') !== '' && valorCampo('idUnidadMedida') !== '';
        }
        if (paso === 3) {
            const inicio = valorCampo('fechainicioActividad');
            const fin = valorCampo('fechafinActividad');
            const cantidad = Number(valorCampo('cantSesionesPlanificada'));
            return inicio !== '' && inicio >= obtenerFechaMinima() && fin >= inicio && Number.isInteger(cantidad) &&
                cantidad >= 1 && cantidad <= 100 && fechasSesiones.length === cantidad && sesionesDentroDeRango();
        }
        if (paso === 4) {
            const personas = Number(valorCampo('cantPersoAtender'));
            return obtenerSeleccionados('idLugarActividad[]').length > 0 && espaciosCompletos() &&
                Number.isInteger(personas) && personas >= 1 && personas <= 99999 &&
                (capacidadMinima === 0 || personas <= capacidadMinima) && obtenerSeleccionados('idHorario[]').length > 0 &&
                horariosSesionesCompletos();
        }
        if (paso === 5) {
            return obtenerSeleccionados('idEmpleado[]').length > 0 && obtenerSeleccionados('idDocente[]').length > 0;
        }
        return true;
    }

    function mostrarError(nombre, mensaje) {
        const error = document.getElementById('error-' + nombre);
        if (error) {
            error.textContent = mensaje;
            error.style.display = 'block';
        }
    }

    function limpiarErrores() {
        const form = document.getElementById('formActividad');
        form.querySelectorAll('.invalid-feedback').forEach(function (error) {
            error.textContent = '';
            error.style.display = 'none';
        });
        form.querySelectorAll('.is-invalid').forEach(function (campo) {
            campo.classList.remove('is-invalid');
        });
        document.getElementById('error-capacidad').style.display = 'none';
    }

    // Fechas y sesiones
    function inicializarFechas() {
        const inicio = document.getElementById('fechaInicio');
        const fin = document.getElementById('fechaFin');

        function cambioFechas() {
            conservarSesionesIniciales = false;
            generarSesiones();
            programarDisponibilidad();
            programarOcupacion();
        }

        inicio.addEventListener('change', cambioFechas);
        fin.addEventListener('change', cambioFechas);
        document.getElementById('cantSesiones').addEventListener('input', function () {
            conservarSesionesIniciales = false;
            clearTimeout(timerSesiones);
            timerSesiones = setTimeout(function () {
                generarSesiones();
                programarDisponibilidad();
                programarOcupacion();
            }, 250);
        });
    }

    function diasDelRango(inicio, fin) {
        const dias = [];
        if (!inicio || !fin || fin < inicio) return dias;
        let actual = new Date(inicio + 'T00:00:00Z');
        const limite = new Date(fin + 'T00:00:00Z');
        while (actual <= limite) {
            dias.push(actual.toISOString().slice(0, 10));
            actual.setUTCDate(actual.getUTCDate() + 1);
        }
        return dias;
    }

    function alinearSesiones() {
        const total = fechasSesiones.length;
        while (horariosSesiones.length < total) horariosSesiones.push('');
        while (lugaresSesiones.length < total) lugaresSesiones.push('');
        while (docentesSesiones.length < total) docentesSesiones.push(null);
        horariosSesiones.length = total;
        lugaresSesiones.length = total;
        docentesSesiones.length = total;

        const tocadas = new Set();
        sesionesDocentesTocadas.forEach(function (indice) {
            if (indice < total) tocadas.add(indice);
        });
        sesionesDocentesTocadas = tocadas;
    }

    function generarSesiones() {
        const inicio = valorCampo('fechainicioActividad');
        const fin = valorCampo('fechafinActividad');
        const cantidad = Number(valorCampo('cantSesionesPlanificada'));
        const info = document.getElementById('info-dias-rango');
        fechasSesiones = [];

        if (!inicio || inicio < obtenerFechaMinima() || !fin || fin < inicio || !Number.isInteger(cantidad) || cantidad < 1 || cantidad > 100) {
            alinearSesiones();
            mostrarSesiones();
            mostrarPlanificacionSesiones();
            actualizarInfoRango();
            actualizarResumen();
            return;
        }

        const dias = diasDelRango(inicio, fin);
        info.className = 'text-muted d-block mt-2';

        if (dias.length >= cantidad) {
            info.textContent = dias.length + ' día(s) disponible(s) en el rango. Puede ajustar la fecha de cada sesión, incluso repetirlas.';
            if (cantidad === 1) {
                fechasSesiones = [dias[0]];
            } else {
                for (let i = 0; i < cantidad; i++) {
                    const indice = Math.floor(i * (dias.length - 1) / (cantidad - 1));
                    fechasSesiones.push(dias[indice]);
                }
            }
        } else {
            // Hay más sesiones que días: se repiten fechas (sesiones simultáneas o en distinto horario)
            info.textContent = 'Solo hay ' + dias.length + ' día(s) en el rango: algunas sesiones compartirán fecha; diferéncielas por horario o lugar.';
            for (let i = 0; i < cantidad; i++) {
                fechasSesiones.push(dias[i % dias.length]);
            }
        }

        alinearSesiones();
        mostrarSesiones();
        mostrarPlanificacionSesiones();
        actualizarResumen();
    }

    function actualizarInfoRango() {
        const info = document.getElementById('info-dias-rango');
        const inicio = valorCampo('fechainicioActividad');
        const fin = valorCampo('fechafinActividad');
        if (!inicio || !fin) {
            info.className = 'text-muted d-block mt-2';
            info.textContent = 'Seleccione las fechas para ver los días disponibles.';
        } else if (inicio < obtenerFechaMinima()) {
            info.className = 'text-danger d-block mt-2';
            info.textContent = 'La fecha mínima permitida es ' + formatearFecha(obtenerFechaMinima()) + ' (hasta 15 días antes de hoy).';
        } else if (fin < inicio) {
            info.className = 'text-danger d-block mt-2';
            info.textContent = 'La fecha de fin no puede ser anterior a la de inicio.';
        } else {
            const dias = diasDelRango(inicio, fin);
            info.className = 'text-muted d-block mt-2';
            info.textContent = dias.length + ' día(s) disponible(s) en el rango.';
        }
    }

    function mostrarSesiones() {
        const contenedor = document.getElementById('sesiones-container');
        const lista = document.getElementById('sesiones-list');
        const inicio = valorCampo('fechainicioActividad');
        const fin = valorCampo('fechafinActividad');

        lista.innerHTML = fechasSesiones.map(function (fecha, indice) {
            return '<div class="sesion-item" data-indice="' + indice + '">' +
                '<span class="sesion-titulo"><i class="bi bi-calendar-check"></i>Sesión ' + (indice + 1) + '</span>' +
                '<input type="date" class="form-control form-control-sm sesion-fecha" data-indice="' + indice + '" value="' + escapar(fecha) + '"' +
                    (inicio ? ' min="' + inicio + '"' : '') + (fin ? ' max="' + fin + '"' : '') +
                    ' title="Fecha de la sesión (editable)">' +
            '</div>';
        }).join('');
        contenedor.style.display = fechasSesiones.length ? 'block' : 'none';
    }

    function horariosParaSesiones() {
        return Array.from(document.querySelectorAll('input[name="idHorario[]"]:checked')).map(function (input) {
            const contenido = input.closest('label').querySelector('.horario-content');
            return { id: input.value, nombre: contenido ? contenido.textContent.trim() : input.value };
        });
    }

    function lugaresParaSesiones() {
        return Array.from(document.querySelectorAll('input[name="idLugarActividad[]"]:checked')).map(function (input) {
            return { id: input.value, nombre: input.dataset.nombre || input.value };
        });
    }

    function docentesParaSesiones() {
        return Array.from(document.querySelectorAll('input[name="idDocente[]"]:checked')).map(function (input) {
            return { id: input.value, nombre: input.dataset.nombre || input.value };
        });
    }

    function nombreHorario(idHorario) {
        const input = document.querySelector('input[name="idHorario[]"][value="' + idHorario + '"]');
        if (!input) return '-';
        const contenido = input.closest('label').querySelector('.horario-content');
        return contenido ? contenido.textContent.trim() : idHorario;
    }

    function nombreLugar(idLugar) {
        const input = document.querySelector('input[name="idLugarActividad[]"][value="' + idLugar + '"]');
        return input ? (input.dataset.nombre || idLugar) : '-';
    }

    function nombreDocente(idDocente) {
        const input = document.querySelector('input[name="idDocente[]"][value="' + idDocente + '"]');
        return input ? (input.dataset.nombre || idDocente) : idDocente;
    }

    // Horario y lugar por sesión (paso 4)
    function mostrarPlanificacionSesiones() {
        const contenedor = document.getElementById('horarios-sesiones-container');
        const lista = document.getElementById('sesiones-horarios-list');
        if (!contenedor || !lista) return;

        alinearSesiones();
        const horarios = horariosParaSesiones();
        const lugares = lugaresParaSesiones();

        fechasSesiones.forEach(function (fecha, indice) {
            const horarioActual = horariosSesiones[indice];
            if (!horarios.some(function (h) { return h.id === horarioActual; })) {
                horariosSesiones[indice] = horarios.length ? horarios[0].id : '';
            }
            const lugarActual = lugaresSesiones[indice];
            if (!lugares.some(function (l) { return l.id === lugarActual; })) {
                lugaresSesiones[indice] = lugares.length ? lugares[0].id : '';
            }
        });

        lista.innerHTML = fechasSesiones.map(function (fecha, indice) {
            const opcionesHorario = horarios.map(function (h) {
                return '<option value="' + escapar(h.id) + '"' +
                    (horariosSesiones[indice] === h.id ? ' selected' : '') + '>' + escapar(h.nombre) + '</option>';
            }).join('');
            const opcionesLugar = lugares.map(function (l) {
                return '<option value="' + escapar(l.id) + '"' +
                    (lugaresSesiones[indice] === l.id ? ' selected' : '') + '>' + escapar(l.nombre) + '</option>';
            }).join('');
            return '<div class="sesion-item sesion-horario-item" data-indice="' + indice + '">' +
                '<span class="sesion-titulo"><i class="bi bi-clock"></i>Sesión ' + (indice + 1) + '</span>' +
                '<span class="sesion-fecha-texto">' + escapar(formatearFecha(fecha)) + '</span>' +
                '<select class="form-select form-select-sm sesion-horario" data-indice="' + indice + '" title="Horario de la sesión">' +
                    (opcionesHorario || '<option value="">Seleccione primero un horario</option>') +
                '</select>' +
                '<select class="form-select form-select-sm sesion-lugar" data-indice="' + indice + '" title="Lugar de la sesión">' +
                    (opcionesLugar || '<option value="">Seleccione primero un lugar</option>') +
                '</select>' +
            '</div>';
        }).join('');

        contenedor.style.display = fechasSesiones.length ? 'block' : 'none';
    }

    // Docentes por sesión (paso 5): pre-marcados los disponibles, ocupados con advertencia
    function mostrarDocentesSesiones() {
        const contenedor = document.getElementById('docentes-sesiones-container');
        const lista = document.getElementById('docentes-sesiones-list');
        if (!contenedor || !lista) return;

        alinearSesiones();
        const docentes = docentesParaSesiones();

        lista.innerHTML = fechasSesiones.map(function (fecha, indice) {
            const asignados = Array.isArray(docentesSesiones[indice]) ? docentesSesiones[indice] : [];
            const ocupados = ocupadosPorSesion[indice] || [];
            const chips = docentes.map(function (docente) {
                const activo = asignados.indexOf(docente.id) !== -1;
                const ocupado = ocupados.indexOf(docente.id) !== -1;
                return '<label class="docente-sesion-chip' + (activo ? ' activo' : '') + (ocupado ? ' ocupado' : '') + '"' +
                    (ocupado ? ' title="Tiene otra actividad en esta fecha y horario; no se podrá guardar si queda asignado"' : '') + '>' +
                    '<input type="checkbox" class="docente-sesion-check" data-indice="' + indice + '" value="' + escapar(docente.id) + '"' +
                        (activo ? ' checked' : '') + '>' +
                    '<span>' + (ocupado ? '⚠ ' : '') + escapar(docente.nombre) + '</span>' +
                '</label>';
            }).join('');
            return '<div class="docente-sesion-item" data-indice="' + indice + '">' +
                '<span class="sesion-titulo"><i class="bi bi-person-badge"></i>Sesión ' + (indice + 1) + '</span>' +
                '<span class="sesion-fecha-texto">' + escapar(formatearFecha(fecha)) + ' · ' + escapar(nombreHorario(horariosSesiones[indice])) +
                    ' · ' + escapar(nombreLugar(lugaresSesiones[indice])) + '</span>' +
                '<div class="docente-sesion-chips">' + (chips || '<span class="text-muted small">Seleccione primero los docentes de la actividad</span>') + '</div>' +
            '</div>';
        }).join('');

        contenedor.style.display = fechasSesiones.length ? 'block' : 'none';
    }

    function programarOcupacion() {
        clearTimeout(timerOcupacion);
        timerOcupacion = setTimeout(consultarOcupacionSesiones, 250);
    }

    // Consulta qué docentes están ocupados en cada sesión (fecha + horario) por otras actividades.
    // Las sesiones no ajustadas manualmente se rellenan con los docentes disponibles.
    function consultarOcupacionSesiones() {
        if (!fechasSesiones.length) return Promise.resolve();

        const params = new URLSearchParams();
        params.set('action', 'previsualizarSesiones');
        params.set('idExcluir', window.idActividadActual || '');
        fechasSesiones.forEach(function (fecha, indice) {
            params.append('fechasSesiones[]', fecha);
            params.append('sesionesHorario[]', horariosSesiones[indice] || '');
        });

        const numeroConsulta = ++consultaOcupacion;
        return fetch('index.php?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) throw new Error('Error de ocupación');
                return response.json();
            })
            .then(function (data) {
                if (numeroConsulta !== consultaOcupacion) return;
                ocupadosPorSesion = data.ocupadosPorSesion || {};

                const seleccionados = obtenerSeleccionados('idDocente[]');
                fechasSesiones.forEach(function (fecha, indice) {
                    if (sesionesDocentesTocadas.has(indice)) {
                        // Ajustada manualmente: solo se depuran docentes deseleccionados de la actividad
                        docentesSesiones[indice] = (Array.isArray(docentesSesiones[indice]) ? docentesSesiones[indice] : []).filter(function (id) {
                            return seleccionados.indexOf(id) !== -1;
                        });
                        return;
                    }
                    // Automática: participan los docentes seleccionados y disponibles en esa fecha+horario
                    const ocupados = ocupadosPorSesion[indice] || [];
                    docentesSesiones[indice] = seleccionados.filter(function (id) {
                        return ocupados.indexOf(id) === -1;
                    });
                });

                mostrarDocentesSesiones();
                if (pasoActual === totalPasos) generarResumenFinal();
                actualizarResumen();
            })
            .catch(function () {
                // Sin datos de ocupación: se mantienen las asignaciones actuales
            });
    }

    function inicializarSesionesManuales() {
        document.getElementById('sesiones-list').addEventListener('change', function (event) {
            const indice = Number(event.target.dataset.indice);
            if (!Number.isInteger(indice) || !fechasSesiones[indice]) return;

            if (event.target.classList.contains('sesion-fecha')) {
                fechasSesiones[indice] = event.target.value;
                mostrarPlanificacionSesiones();
                programarDisponibilidad();
                programarOcupacion();
            }
            actualizarResumen();
        });

        document.getElementById('sesiones-horarios-list').addEventListener('change', function (event) {
            const indice = Number(event.target.dataset.indice);
            if (!Number.isInteger(indice) || !fechasSesiones[indice]) return;

            if (event.target.classList.contains('sesion-horario')) {
                horariosSesiones[indice] = event.target.value;
                programarDisponibilidad();
                programarOcupacion();
            } else if (event.target.classList.contains('sesion-lugar')) {
                lugaresSesiones[indice] = event.target.value;
                mostrarDocentesSesiones();
            }
            actualizarResumen();
        });

        document.getElementById('docentes-sesiones-list').addEventListener('change', function (event) {
            if (!event.target.classList.contains('docente-sesion-check')) return;
            const indice = Number(event.target.dataset.indice);
            if (!Number.isInteger(indice) || !fechasSesiones[indice]) return;

            const actuales = Array.isArray(docentesSesiones[indice]) ? docentesSesiones[indice].slice() : [];
            const posicion = actuales.indexOf(event.target.value);
            if (event.target.checked && posicion === -1) {
                actuales.push(event.target.value);
            } else if (!event.target.checked && posicion !== -1) {
                actuales.splice(posicion, 1);
            }
            docentesSesiones[indice] = actuales;
            sesionesDocentesTocadas.add(indice);
            mostrarDocentesSesiones();
            actualizarResumen();
        });
    }

    // Selectores múltiples
    function normalizarTexto(valor) {
        return String(valor || '').toLocaleLowerCase('es').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function inicializarSelectores() {
        const selectores = Array.from(document.querySelectorAll('.selector-multiple'));

        function cerrar(selector) {
            selector.classList.remove('abierto');
            const toggle = selector.querySelector('.selector-toggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }

        selectores.forEach(function (selector) {
            const toggle = selector.querySelector('.selector-toggle');
            const buscador = selector.querySelector('[data-buscar]');
            const opciones = selector.querySelector('.selector-opciones');
            const sinResultados = document.createElement('div');
            sinResultados.className = 'selector-sin-resultados';
            sinResultados.textContent = 'No se encontraron resultados.';
            sinResultados.hidden = true;
            opciones.appendChild(sinResultados);

            toggle.addEventListener('click', function () {
                const abrir = !selector.classList.contains('abierto');
                selectores.forEach(cerrar);
                if (abrir) {
                    selector.classList.add('abierto');
                    toggle.setAttribute('aria-expanded', 'true');
                    buscador.value = '';
                    buscador.dispatchEvent(new Event('input'));
                    setTimeout(function () { buscador.focus(); }, 0);
                }
            });

            buscador.addEventListener('input', function () {
                const consulta = normalizarTexto(buscador.value.trim());
                let visibles = 0;
                opciones.querySelectorAll('.selector-opcion').forEach(function (opcion) {
                    const visible = normalizarTexto(opcion.textContent).indexOf(consulta) !== -1;
                    opcion.style.display = visible ? 'flex' : 'none';
                    if (visible) visibles++;
                });
                sinResultados.hidden = visibles !== 0;
            });

            selector.addEventListener('change', function (event) {
                if (event.target.matches('input[type="checkbox"]')) actualizarResumenSelectores();
            });

            selector.addEventListener('click', function (event) {
                const botonQuitar = event.target.closest('[data-quitar]');
                if (!botonQuitar) return;
                const input = Array.from(selector.querySelectorAll('input[type="checkbox"]')).find(function (opcion) {
                    return opcion.value === botonQuitar.dataset.quitar;
                });
                if (input && input.checked && !input.disabled) {
                    input.checked = false;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.selector-multiple')) selectores.forEach(cerrar);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;
            const abierto = document.querySelector('.selector-multiple.abierto');
            if (!abierto) return;
            const toggle = abierto.querySelector('.selector-toggle');
            cerrar(abierto);
            toggle.focus();
        });
    }

    function actualizarResumenSelectores() {
        const plurales = { lugar: 'lugares', responsable: 'responsables', docente: 'docentes' };
        document.querySelectorAll('.selector-multiple').forEach(function (selector) {
            const inputs = Array.from(selector.querySelectorAll('input[type="checkbox"]'));
            const seleccionados = inputs.filter(function (input) { return input.checked; });
            const entidad = selector.dataset.entidad;
            const resumen = selector.querySelector('[data-resumen]');
            const contenedorChips = selector.querySelector('[data-seleccionados]');

            inputs.forEach(function (input) {
                const opcion = input.closest('.selector-opcion');
                if (opcion) opcion.classList.toggle('seleccionada', input.checked);
            });

            selector.classList.toggle('con-seleccion', seleccionados.length > 0);
            if (!seleccionados.length) {
                resumen.textContent = selector.dataset.placeholder;
            } else {
                const nombreEntidad = seleccionados.length === 1 ? entidad : plurales[entidad];
                resumen.textContent = seleccionados.length + ' ' + nombreEntidad + (seleccionados.length === 1 ? ' seleccionado' : ' seleccionados');
            }

            contenedorChips.innerHTML = '';
            seleccionados.forEach(function (input) {
                const nombre = input.dataset.nombre || input.value;
                const chip = document.createElement('span');
                chip.className = 'selector-chip';
                chip.title = nombre;
                const texto = document.createElement('span');
                texto.textContent = nombre;
                const quitar = document.createElement('button');
                quitar.type = 'button';
                quitar.dataset.quitar = input.value;
                quitar.setAttribute('aria-label', 'Quitar ' + nombre);
                quitar.innerHTML = '&times;';
                chip.append(texto, quitar);
                contenedorChips.appendChild(chip);
            });
        });
    }

    // Lugares, espacios y disponibilidad
    function inicializarSelecciones() {
        document.getElementById('lugares-container').addEventListener('change', function () {
            actualizarEspacios();
            mostrarPlanificacionSesiones();
            programarDisponibilidad();
        });
        document.getElementById('espacios-por-lugar').addEventListener('change', function () {
            actualizarCapacidad();
            programarDisponibilidad();
        });
        document.getElementById('horarios-container').addEventListener('change', function () {
            mostrarPlanificacionSesiones();
            programarDisponibilidad();
            programarOcupacion();
        });
        document.getElementById('docentes-container').addEventListener('change', function () {
            programarOcupacion();
        });
        document.getElementById('cantPersoAtender').addEventListener('input', actualizarCapacidad);
    }

    // Muestra en cada sede solo sus espacios (y los que aún no tienen sede asignada)
    function filtrarEspaciosPorSede(select, idLugar) {
        Array.from(select.options).forEach(function (opt) {
            if (opt.value === '') {
                opt.hidden = false;
                opt.disabled = false;
                return;
            }
            const sede = opt.dataset.sede || '';
            const mostrar = (sede === '' || sede === idLugar || opt.selected);
            opt.hidden = !mostrar;
            opt.disabled = !mostrar;
        });
    }

    function actualizarEspacios() {
        document.querySelectorAll('input[name="idLugarActividad[]"]').forEach(function (lugar) {
            const bloque = document.querySelector('.espacio-lugar[data-lugar="' + lugar.value + '"]');
            if (!bloque) return;
            const visible = lugar.checked && lugar.dataset.sede === '1';
            bloque.style.display = visible ? 'block' : 'none';
            const select = bloque.querySelector('select');
            select.disabled = !visible;
            filtrarEspaciosPorSede(select, lugar.value);
            if (!visible && !lugar.checked) select.value = '';
        });
        actualizarCapacidad();
    }

    function actualizarCapacidad() {
        const capacidades = Array.from(document.querySelectorAll('.espacio-select:not(:disabled)')).filter(function (select) {
            return select.value;
        }).map(function (select) {
            return Number(select.options[select.selectedIndex].dataset.capacidad) || 0;
        });
        capacidadMinima = capacidades.length ? Math.min.apply(null, capacidades) : 0;
        document.getElementById('capacidad-info').style.display = capacidadMinima > 0 ? 'block' : 'none';
        document.getElementById('capacidad-maxima').textContent = capacidadMinima;

        const error = document.getElementById('error-capacidad');
        const personas = Number(valorCampo('cantPersoAtender'));
        const excede = capacidadMinima > 0 && personas > capacidadMinima;
        error.style.display = excede ? 'block' : 'none';
        error.innerHTML = excede ? '<i class="bi bi-exclamation-triangle me-1"></i>La cantidad excede la capacidad disponible (' + capacidadMinima + ').' : '';
    }

    function espaciosCompletos() {
        return obtenerSeleccionados('idLugarActividad[]').every(function (id) {
            const lugar = document.querySelector('input[name="idLugarActividad[]"][value="' + id + '"]');
            if (!lugar || lugar.dataset.sede !== '1') return true;
            const espacio = document.querySelector('.espacio-select[data-lugar="' + id + '"]');
            return espacio && espacio.value !== '';
        });
    }

    function validarEspacios() {
        const completo = espaciosCompletos();
        if (!completo) mostrarError('idEspacioUtilizar', 'Seleccione un espacio para cada sede elegida');
        return completo;
    }

    function programarDisponibilidad() {
        clearTimeout(timerDisponibilidad);
        timerDisponibilidad = setTimeout(consultarDisponibilidad, 250);
    }

    function convertirHora(hora, minutos, periodo) {
        let valor = Number(hora) % 12;
        if (periodo.toUpperCase() === 'PM') valor += 12;
        return valor * 60 + Number(minutos);
    }

    function intervaloHorario(texto) {
        const partes = texto.match(/(\d{1,2}):(\d{2})\s*(AM|PM)\s*-\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i);
        if (!partes) return null;
        return {
            inicio: convertirHora(partes[1], partes[2], partes[3]),
            fin: convertirHora(partes[4], partes[5], partes[6])
        };
    }

    function horariosSeSolapan(primero, segundo) {
        const a = intervaloHorario(primero);
        const b = intervaloHorario(segundo);
        return a && b && a.inicio < b.fin && b.inicio < a.fin;
    }

    // Marca en rojo los horarios que chocan con otra actividad en los lugares y fechas elegidos.
    // Los docentes y responsables ya no se bloquean: el docente se asigna por sesión
    // y el responsable puede estar en varias actividades a la vez.
    function consultarDisponibilidad() {
        const inicio = valorCampo('fechainicioActividad');
        const fin = valorCampo('fechafinActividad');
        const lugares = obtenerSeleccionados('idLugarActividad[]');

        if (!inicio || !fin || fin < inicio || !lugares.length) {
            marcarOcupados('idHorario[]', [], 'Horario ocupado en las fechas y lugares seleccionados');
            return;
        }

        const params = new URLSearchParams();
        params.set('action', 'obtenerHorariosOcupados');
        params.set('fechaInicio', inicio);
        params.set('fechaFin', fin);
        params.set('idExcluir', window.idActividadActual || '');
        fechasSesiones.forEach(function (fecha) { params.append('fechasSesiones[]', fecha); });
        lugares.forEach(function (id) { params.append('idLugares[]', id); });
        document.querySelectorAll('.espacio-select:not(:disabled)').forEach(function (select) {
            if (select.value) params.append('idEspacios[]', select.value);
        });

        const numeroConsulta = ++consultaDisponibilidad;
        fetch('index.php?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) throw new Error('Error de disponibilidad');
                return response.json();
            })
            .then(function (data) {
                if (numeroConsulta !== consultaDisponibilidad) return;
                const horariosExistentes = data.horariosOcupados || [];
                const ocupados = [];
                document.querySelectorAll('input[name="idHorario[]"]').forEach(function (input) {
                    const nombre = input.closest('label').textContent.trim();
                    const ocupado = horariosExistentes.some(function (horario) {
                        return horariosSeSolapan(nombre, horario.nomHorario || '');
                    });
                    if (ocupado) ocupados.push(input.value);
                });
                const quitoHorarios = marcarOcupados('idHorario[]', ocupados, 'Horario ocupado o solapado en las fechas y lugares seleccionados');
                if (quitoHorarios) {
                    mostrarAlerta('warning', 'Se quitaron horarios que ya están ocupados en las fechas o lugares seleccionados.');
                    mostrarPlanificacionSesiones();
                    programarDisponibilidad();
                }
                actualizarResumen();
            })
            .catch(function () {
                if (numeroConsulta === consultaDisponibilidad) {
                    mostrarAlerta('danger', 'No se pudo comprobar la disponibilidad. Inténtelo nuevamente antes de guardar.');
                }
            });
    }

    function marcarOcupados(nombre, ids, titulo) {
        let quitoSeleccion = false;
        document.querySelectorAll('input[name="' + nombre + '"]').forEach(function (input) {
            const ocupado = ids.indexOf(input.value) !== -1;
            if (ocupado && input.checked) quitoSeleccion = true;
            if (ocupado) input.checked = false;
            input.disabled = ocupado;
            const etiqueta = input.closest('label');
            etiqueta.classList.toggle('occupied', ocupado);
            etiqueta.title = ocupado ? titulo : '';
        });
        actualizarResumenSelectores();
        return quitoSeleccion;
    }

    // Modales de catálogos
    let cedulaDocenteDuplicada = false;
    let timerCedulaDocente = null;

    // Cascada Estado -> Municipio -> Parroquia del modal de nuevo lugar
    function inicializarCascadaLugar() {
        const selEstado = document.getElementById('idEstadoLugarAct');
        const selMunicipio = document.getElementById('idMunicipioLugarAct');
        const selParroquia = document.getElementById('idParroquiaLugarAct');
        if (!selEstado || !selMunicipio || !selParroquia) return;

        function resetSelect(sel, placeholder, deshabilitar) {
            sel.innerHTML = '<option value="" selected disabled>' + placeholder + '</option>';
            sel.disabled = deshabilitar;
        }

        selEstado.addEventListener('change', function () {
            resetSelect(selMunicipio, 'Cargando municipios...', true);
            resetSelect(selParroquia, 'Seleccione primero un municipio...', true);
            fetch('index.php?action=municipiosPorEstado&idEstado=' + encodeURIComponent(this.value))
                .then(function (r) { return r.json(); })
                .then(function (respuesta) {
                    if (respuesta.status === 'success' && respuesta.municipios.length > 0) {
                        let opciones = '<option value="" selected disabled>Seleccione un municipio...</option>';
                        respuesta.municipios.forEach(function (m) {
                            opciones += '<option value="' + m.idMunicipio + '">' + m.nombreMunicipio + '</option>';
                        });
                        selMunicipio.innerHTML = opciones;
                        selMunicipio.disabled = false;
                    } else {
                        resetSelect(selMunicipio, 'Sin municipios registrados...', true);
                    }
                })
                .catch(function () { resetSelect(selMunicipio, 'Error al cargar municipios...', true); });
        });

        selMunicipio.addEventListener('change', function () {
            resetSelect(selParroquia, 'Cargando parroquias...', true);
            fetch('index.php?action=parroquiasPorMunicipio&idMunicipio=' + encodeURIComponent(this.value))
                .then(function (r) { return r.json(); })
                .then(function (respuesta) {
                    if (respuesta.status === 'success' && respuesta.parroquias.length > 0) {
                        let opciones = '<option value="" selected disabled>Seleccione una parroquia...</option>';
                        respuesta.parroquias.forEach(function (p) {
                            opciones += '<option value="' + p.idParroquia + '">' + p.nombreParroquia + '</option>';
                        });
                        selParroquia.innerHTML = opciones;
                        selParroquia.disabled = false;
                    } else {
                        resetSelect(selParroquia, 'Sin parroquias registradas...', true);
                    }
                })
                .catch(function () { resetSelect(selParroquia, 'Error al cargar parroquias...', true); });
        });

        // Reiniciar la cascada al cerrar el modal
        const modalLugar = document.getElementById('modalNuevoLugar');
        if (modalLugar) {
            modalLugar.addEventListener('hidden.bs.modal', function () {
                selEstado.value = '';
                resetSelect(selMunicipio, 'Seleccione primero un estado...', true);
                resetSelect(selParroquia, 'Seleccione primero un municipio...', true);
            });
        }
    }

    function inicializarModales() {
        document.getElementById('btnGuardarLugar').addEventListener('click', guardarNuevoLugar);
        document.getElementById('btnGuardarDocente').addEventListener('click', guardarNuevoDocente);
        inicializarCascadaLugar();

        const formDocente = document.getElementById('formNuevoDocente');
        const cedulaDocente = formDocente.querySelector('input[name="cedDocente"]');
        const letraDocente = formDocente.querySelector('select[name="letraCedulaDocente"]');
        if (cedulaDocente) {
            // Cédula solo números + verificación en vivo de duplicados
            cedulaDocente.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 8);
                programarVerificacionCedulaDocente();
            });
            letraDocente.addEventListener('change', programarVerificacionCedulaDocente);

            // Nombres y apellidos sin números; teléfono sin letras
            ['nombreDocente', 'apellidoDocente'].forEach(function (nombre) {
                formDocente.querySelector('input[name="' + nombre + '"]').addEventListener('input', function () {
                    this.value = this.value.replace(/[0-9]/g, '');
                });
            });
            formDocente.querySelector('input[name="telfDocente"]').addEventListener('input', function () {
                this.value = this.value.replace(/[A-Za-z]/g, '');
            });
        }
    }

    function programarVerificacionCedulaDocente() {
        clearTimeout(timerCedulaDocente);
        timerCedulaDocente = setTimeout(verificarCedulaDocente, 400);
    }

    function verificarCedulaDocente() {
        const form = document.getElementById('formNuevoDocente');
        const numero = form.querySelector('input[name="cedDocente"]').value.trim();
        const letra = form.querySelector('select[name="letraCedulaDocente"]').value;
        const error = document.getElementById('error-cedDocente');
        cedulaDocenteDuplicada = false;
        if (!/^\d{6,8}$/.test(numero)) {
            error.style.display = 'none';
            return;
        }
        const cedulaCompleta = letra + numero;
        fetch('index.php?action=verificarCedulaDocente&cedula=' + encodeURIComponent(cedulaCompleta))
            .then(function (response) { return response.json(); })
            .then(function (respuesta) {
                if (respuesta.existe) {
                    cedulaDocenteDuplicada = true;
                    error.textContent = 'Esta cédula ya está registrada (' + cedulaCompleta + '). No se permiten duplicados.';
                    error.style.display = 'block';
                } else {
                    error.style.display = 'none';
                }
            })
            .catch(function () { /* sin verificación: el servidor valida al guardar */ });
    }

    function guardarNuevoLugar() {
        const form = document.getElementById('formNuevoLugar');
        const data = new FormData(form);
        if (!data.get('nomLugarActividad').trim() || !data.get('direccion').trim() || !data.get('idParroquia')) {
            mostrarAlerta('danger', 'Nombre, dirección y parroquia son obligatorios.');
            return;
        }

        enviarCatalogo('guardarLugarActividad', data, document.getElementById('btnGuardarLugar'), function (respuesta) {
            agregarLugar(respuesta.id, data.get('nomLugarActividad').trim(), data.get('esSede') === '1');
            form.reset();
            cerrarModal('modalNuevoLugar');
        });
    }

    function guardarNuevoDocente() {
        const form = document.getElementById('formNuevoDocente');
        const data = new FormData(form);
        const letra = data.get('letraCedulaDocente') || 'V';
        const numero = (data.get('cedDocente') || '').trim();

        if (!/^\d{6,8}$/.test(numero)) {
            mostrarAlerta('danger', 'Ingrese la cédula solo con números (6 a 8 dígitos).');
            return;
        }
        if (!data.get('nombreDocente').trim() || !data.get('apellidoDocente').trim()) {
            mostrarAlerta('danger', 'Cédula, nombres y apellidos son obligatorios.');
            return;
        }
        if (/\d/.test(data.get('nombreDocente')) || /\d/.test(data.get('apellidoDocente'))) {
            mostrarAlerta('danger', 'Los nombres y apellidos no pueden contener números.');
            return;
        }
        if (/[A-Za-z]/.test(data.get('telfDocente') || '')) {
            mostrarAlerta('danger', 'El teléfono no puede contener letras.');
            return;
        }
        const telefonoDocente = (data.get('telfDocente') || '').trim();
        if (!telefonoDocente) {
            mostrarAlerta('danger', 'El teléfono es obligatorio.');
            return;
        }
        if (telefonoDocente.replace(/\D/g, '').length < 10) {
            mostrarAlerta('danger', 'Ingrese un teléfono válido (mínimo 10 dígitos, Ej: 0412-1234567).');
            return;
        }
        if (cedulaDocenteDuplicada) {
            mostrarAlerta('danger', 'Esta cédula ya está registrada. No se permiten duplicados.');
            return;
        }

        // La nacionalidad se deduce del prefijo: V = Venezolano, E = Extranjero
        data.set('cedDocente', letra + numero);
        data.set('nacionalidad', letra === 'E' ? 'Extranjero' : 'Venezolano');

        enviarCatalogo('guardarDocenteAjax', data, document.getElementById('btnGuardarDocente'), function (respuesta) {
            const nombre = data.get('nombreDocente').trim() + ' ' + data.get('apellidoDocente').trim();
            agregarDocente(respuesta.id, nombre, data.get('cedDocente'));
            form.reset();
            cerrarModal('modalNuevoDocente');
        });
    }

    function enviarCatalogo(accion, data, boton, onSuccess) {
        boton.disabled = true;
        const htmlOriginal = boton.innerHTML;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
        fetch('index.php?action=' + accion, { method: 'POST', body: data })
            .then(function (response) {
                if (!response.ok) throw new Error('Error de conexión');
                return response.json();
            })
            .then(function (respuesta) {
                if (respuesta.status !== 'success') throw new Error(respuesta.message || 'No se pudo guardar.');
                mostrarAlerta('success', respuesta.message);
                onSuccess(respuesta);
            })
            .catch(function (error) {
                mostrarAlerta('danger', error.message || 'Error de conexión.');
            })
            .finally(function () {
                boton.disabled = false;
                boton.innerHTML = htmlOriginal;
            });
    }

    function crearOpcionSelector(nombreCampo, id, nombre, detalle, icono) {
        const etiqueta = document.createElement('label');
        etiqueta.className = 'selector-opcion';
        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = nombreCampo;
        input.value = id;
        input.dataset.nombre = nombre;
        input.checked = true;
        const iconoContenedor = document.createElement('span');
        iconoContenedor.className = 'selector-opcion-icono';
        const iconoElemento = document.createElement('i');
        iconoElemento.className = 'bi ' + icono;
        iconoContenedor.appendChild(iconoElemento);
        const texto = document.createElement('span');
        texto.className = 'selector-opcion-texto';
        const nombreElemento = document.createElement('strong');
        nombreElemento.textContent = nombre;
        const detalleElemento = document.createElement('small');
        detalleElemento.textContent = detalle;
        texto.append(nombreElemento, detalleElemento);
        const check = document.createElement('i');
        check.className = 'bi bi-check-lg selector-check';
        etiqueta.append(input, iconoContenedor, texto, check);
        return etiqueta;
    }

    function agregarLugar(id, nombre, esSede) {
        const etiqueta = crearOpcionSelector(
            'idLugarActividad[]', id, nombre,
            esSede ? 'Sede institucional' : 'Lugar externo', 'bi-geo-alt'
        );
        etiqueta.querySelector('input').dataset.sede = esSede ? '1' : '0';
        const contenedor = document.getElementById('lugares-container');
        contenedor.appendChild(etiqueta);
        contenedor.closest('.selector-multiple').querySelector('[data-buscar]').dispatchEvent(new Event('input'));

        const modelo = document.querySelector('.espacio-select');
        const bloque = document.createElement('div');
        bloque.className = 'espacio-lugar mb-2';
        bloque.dataset.lugar = id;
        bloque.dataset.sede = esSede ? '1' : '0';
        const labelEspacio = document.createElement('label');
        labelEspacio.className = 'form-label small mb-1';
        labelEspacio.textContent = nombre;
        const select = modelo.cloneNode(true);
        select.name = 'idEspacioUtilizar[' + id + ']';
        select.dataset.lugar = id;
        select.value = '';
        select.disabled = !esSede;
        filtrarEspaciosPorSede(select, id);
        bloque.append(labelEspacio, select);
        document.getElementById('espacios-por-lugar').appendChild(bloque);
        actualizarEspacios();
        actualizarResumenSelectores();
        mostrarPlanificacionSesiones();
        programarDisponibilidad();
        actualizarResumen();
    }

    function agregarDocente(id, nombre, cedula) {
        const etiqueta = crearOpcionSelector('idDocente[]', id, nombre, 'C.I. ' + cedula, 'bi-person-badge');
        const contenedor = document.getElementById('docentes-container');
        contenedor.appendChild(etiqueta);
        contenedor.closest('.selector-multiple').querySelector('[data-buscar]').dispatchEvent(new Event('input'));
        actualizarResumenSelectores();
        programarOcupacion();
        actualizarResumen();
    }

    function cerrarModal(id) {
        const elemento = document.getElementById(id);
        const modal = bootstrap.Modal.getInstance(elemento) || new bootstrap.Modal(elemento);
        modal.hide();
    }

    // Resúmenes
    function inicializarResumen() {
        const form = document.getElementById('formActividad');
        form.addEventListener('change', actualizarResumen);
        form.addEventListener('input', debounce(actualizarResumen, 250));
    }

    function filaResumen(label, valor) {
        return '<div class="carrito-item"><span class="carrito-label">' + escapar(label) + '</span><span class="carrito-value">' + escapar(valor || '-') + '</span></div>';
    }

    function actualizarResumen() {
        let progreso = 0;
        const vacio = document.querySelector('.carrito-vacio');
        const secciones = [
            {
                id: 1,
                visible: valorCampo('nombreActividad') !== '',
                html: filaResumen('Nombre', valorCampo('nombreActividad')) + filaResumen('Tipo', valorCampo('idTipoActividad') ? textoSelect('idTipoActividad') : '-') + filaResumen('Área', valorCampo('idAreaE') ? textoSelect('idAreaE') : '-')
            },
            {
                id: 2,
                visible: obtenerSeleccionados('gruposEtarios[]').length > 0,
                html: filaResumen('Grupos etarios', obtenerTextos('gruposEtarios[]').join(', ')) + filaResumen('Unidad', valorCampo('idUnidadMedida') ? textoSelect('idUnidadMedida') : '-')
            },
            {
                id: 3,
                visible: valorCampo('fechainicioActividad') !== '' || valorCampo('fechafinActividad') !== '',
                html: filaResumen('Inicio', formatearFecha(valorCampo('fechainicioActividad'))) + filaResumen('Fin', formatearFecha(valorCampo('fechafinActividad'))) + filaResumen('Sesiones', fechasSesiones.length + ' de ' + valorCampo('cantSesionesPlanificada'))
            },
            {
                id: 4,
                visible: obtenerSeleccionados('idLugarActividad[]').length > 0,
                html: filaResumen('Lugares', obtenerTextos('idLugarActividad[]').join(', ')) + filaResumen('Horarios', obtenerTextos('idHorario[]').join(', ')) + filaResumen('Personas', valorCampo('cantPersoAtender'))
            },
            {
                id: 5,
                visible: obtenerSeleccionados('idEmpleado[]').length > 0 || obtenerSeleccionados('idDocente[]').length > 0,
                html: filaResumen('Responsables', obtenerTextos('idEmpleado[]').join(', ')) + filaResumen('Docentes', obtenerTextos('idDocente[]').join(', '))
            }
        ];

        secciones.forEach(function (seccion) {
            document.getElementById('carrito-paso' + seccion.id).style.display = seccion.visible ? 'block' : 'none';
            document.getElementById('carrito-body-paso' + seccion.id).innerHTML = seccion.visible ? seccion.html : '';
            if (seccion.visible) progreso += 100 / totalPasos;
        });
        vacio.style.display = progreso === 0 ? 'block' : 'none';
        document.getElementById('carrito-progreso').textContent = Math.round(progreso) + '%';
        document.getElementById('carrito-progress-bar').style.width = progreso + '%';
    }

    function generarResumenFinal() {
        const filas = [
            ['Nombre', valorCampo('nombreActividad')],
            ['Objetivo', valorCampo('objetivoActividad')],
            ['Descripción', valorCampo('descActividad')],
            ['Tipo', textoSelect('idTipoActividad')],
            ['Vértice', textoSelect('idVertice')],
            ['Área', textoSelect('idAreaE')],
            ['Estrategia', textoSelect('idEstDesarrollo')],
            ['Grupos etarios', obtenerTextos('gruposEtarios[]').join(', ')],
            ['Grupo étnico', textoSelect('idGrupoEtnio')],
            ['Unidad', textoSelect('idUnidadMedida')],
            ['Fechas', formatearFecha(valorCampo('fechainicioActividad')) + ' — ' + formatearFecha(valorCampo('fechafinActividad'))],
            ['Lugares', obtenerTextos('idLugarActividad[]').join(', ')],
            ['Espacios', Array.from(document.querySelectorAll('.espacio-select:not(:disabled)')).filter(function (select) { return select.value; }).map(function (select) { return select.options[select.selectedIndex].textContent; }).join(', ') || 'No aplica'],
            ['Horarios', obtenerTextos('idHorario[]').join(', ')],
            ['Personas', valorCampo('cantPersoAtender')],
            ['Responsables', obtenerTextos('idEmpleado[]').join(', ')],
            ['Docentes', obtenerTextos('idDocente[]').join(', ')],
            ['Observaciones', valorCampo('observacion') || 'Sin observaciones']
        ];

        const sesionesHtml = fechasSesiones.map(function (fecha, indice) {
            const asignados = Array.isArray(docentesSesiones[indice]) ? docentesSesiones[indice] : [];
            const ocupados = ocupadosPorSesion[indice] || [];
            const hayOcupado = asignados.some(function (id) { return ocupados.indexOf(id) !== -1; });
            const docentesTxt = asignados.length
                ? escapar(asignados.map(function (id) { return nombreDocente(id); }).join(', '))
                : '<span class="text-danger">Sin docente asignado</span>';
            return '<li><strong>S' + (indice + 1) + '</strong>: ' + escapar(formatearFecha(fecha)) +
                ' · ' + escapar(nombreHorario(horariosSesiones[indice])) +
                ' · ' + escapar(nombreLugar(lugaresSesiones[indice])) +
                ' · ' + docentesTxt +
                (hayOcupado ? ' <span class="text-danger small">(⚠ un docente no está disponible en ese horario; corríjalo antes de guardar)</span>' : '') +
                '</li>';
        }).join('');

        document.getElementById('resumen-content').innerHTML = '<div class="resumen-section">' + filas.map(function (fila) {
            return '<div class="resumen-row"><span class="label">' + escapar(fila[0]) + ':</span><span class="value">' + escapar(fila[1] || '-') + '</span></div>';
        }).join('') + '</div>' +
        '<div class="resumen-section"><div class="resumen-row"><span class="label">Sesiones (fecha · horario · lugar · docentes):</span></div>' +
        '<ul class="resumen-sesiones-lista">' + (sesionesHtml || '<li><span class="text-muted">No hay sesiones planificadas.</span></li>') + '</ul></div>';

        programarOcupacion();
    }

    // Guardar
    function guardarActividad() {
        if (guardando) return;
        for (let i = 1; i <= 5; i++) {
            if (!validarPasoSilencioso(i)) {
                irAPaso(i);
                validarPaso(i);
                mostrarAlerta('danger', 'Hay campos incompletos o con errores. Revise el paso ' + i + '.');
                return;
            }
        }

        guardando = true;
        const boton = document.getElementById('btnGuardar');
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

        // Se consulta la ocupación más reciente y se validan las reglas de sesiones antes de enviar
        consultarOcupacionSesiones().then(function () {
            const reglaSesiones = validarReglasSesiones();
            if (reglaSesiones) {
                guardando = false;
                boton.disabled = false;
                boton.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar Actividad';
                irAPaso(reglaSesiones.paso);
                validarPaso(reglaSesiones.paso);
                mostrarAlerta('danger', reglaSesiones.mensaje);
                return;
            }
            enviarActividad(boton);
        });
    }

    function enviarActividad(boton) {
        const data = new FormData(document.getElementById('formActividad'));
        fechasSesiones.forEach(function (fecha, indice) {
            data.append('fechasSesiones[]', fecha);
            data.append('sesionesHorario[]', horariosSesiones[indice] || '');
            data.append('sesionesLugar[]', lugaresSesiones[indice] || '');
            const asignados = Array.isArray(docentesSesiones[indice]) ? docentesSesiones[indice] : [];
            data.append('sesionesDocentes[]', asignados.join(','));
        });
        const accion = window.modoEdicion ? 'editarActividad' : 'guardarActividad';

        fetch('index.php?action=' + accion, { method: 'POST', body: data })
            .then(function (response) {
                if (!response.ok) throw new Error('Error de conexión (' + response.status + ').');
                return response.json();
            })
            .then(function (respuesta) {
                if (respuesta.status !== 'success') throw new Error(respuesta.message || 'No se pudo guardar la actividad.');
                mostrarAlerta('success', respuesta.message);
                window.location.href = 'index.php?action=actividades';
            })
            .catch(function (error) {
                mostrarAlerta('danger', error.message || 'Error de conexión.');
                guardando = false;
                boton.disabled = false;
                boton.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar Actividad';
            });
    }

    function mostrarAlerta(tipo, mensaje) {
        Validar.aviso(mensaje, tipo);
    }
})();
