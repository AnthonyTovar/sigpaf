// Registro y edición de actividades


(function () {
    'use strict';

    const totalPasos = 6;
    let pasoActual = 1;
    let fechasSesiones = [];
    let conservarSesionesIniciales = Boolean(window.modoEdicion);
    let capacidadMinima = 0;
    let guardando = false;
    let consultaDisponibilidad = 0;
    let timerDisponibilidad = null;
    let timerSesiones = null;

    document.addEventListener('DOMContentLoaded', function () {
        inicializarWizard();
        inicializarFechas();
        inicializarSelectores();
        inicializarSelecciones();
        inicializarResumen();
        inicializarModales();

        if (conservarSesionesIniciales && Array.isArray(window.fechasSesionesIniciales)) {
            fechasSesiones = window.fechasSesionesIniciales.slice();
            mostrarSesiones();
            actualizarInfoRango();
        } else {
            generarSesiones();
        }

        actualizarEspacios();
        actualizarResumenSelectores();
        consultarDisponibilidad();
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
                mostrarError('cantSesionesPlanificada', 'No hay suficientes días disponibles para todas las sesiones');
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
        } else if (paso === 5) {
            valido = validarGrupo('idEmpleado[]', 'idEmpleado', 'Seleccione al menos un responsable') && valido;
            valido = validarGrupo('idDocente[]', 'idDocente', 'Seleccione al menos un docente disponible') && valido;
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
            return inicio !== '' && inicio >= obtenerFechaMinima() && fin >= inicio && Number.isInteger(cantidad) && cantidad >= 1 && cantidad <= 100 && fechasSesiones.length === cantidad;
        }
        if (paso === 4) {
            const personas = Number(valorCampo('cantPersoAtender'));
            return obtenerSeleccionados('idLugarActividad[]').length > 0 && espaciosCompletos() &&
                Number.isInteger(personas) && personas >= 1 && personas <= 99999 &&
                (capacidadMinima === 0 || personas <= capacidadMinima) && obtenerSeleccionados('idHorario[]').length > 0;
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
        }

        inicio.addEventListener('change', cambioFechas);
        fin.addEventListener('change', cambioFechas);
        document.getElementById('cantSesiones').addEventListener('input', function () {
            conservarSesionesIniciales = false;
            clearTimeout(timerSesiones);
            timerSesiones = setTimeout(function () {
                generarSesiones();
                programarDisponibilidad();
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

    function generarSesiones() {
        const inicio = valorCampo('fechainicioActividad');
        const fin = valorCampo('fechafinActividad');
        const cantidad = Number(valorCampo('cantSesionesPlanificada'));
        const info = document.getElementById('info-dias-rango');
        fechasSesiones = [];

        if (!inicio || inicio < obtenerFechaMinima() || !fin || fin < inicio || !Number.isInteger(cantidad) || cantidad < 1 || cantidad > 100) {
            mostrarSesiones();
            actualizarInfoRango();
            actualizarResumen();
            return;
        }

        const dias = diasDelRango(inicio, fin);
        info.className = 'text-muted d-block mt-2';
        info.textContent = dias.length + ' día(s) disponible(s) en el rango. La disponibilidad se comprobará por lugar, horario y docente.';

        if (dias.length < cantidad) {
            info.className = 'text-danger d-block mt-2';
            info.textContent = 'Solo hay ' + dias.length + ' día(s) en el rango: amplíe las fechas o reduzca las sesiones.';
        } else if (cantidad === 1) {
            fechasSesiones = [dias[0]];
        } else {
            for (let i = 0; i < cantidad; i++) {
                const indice = Math.floor(i * (dias.length - 1) / (cantidad - 1));
                fechasSesiones.push(dias[indice]);
            }
        }

        mostrarSesiones();
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
        lista.innerHTML = fechasSesiones.map(function (fecha, indice) {
            return '<span class="sesion-badge"><i class="bi bi-calendar-check"></i>Sesión ' + (indice + 1) + ': ' + escapar(formatearFecha(fecha)) + '</span>';
        }).join('');
        contenedor.style.display = fechasSesiones.length ? 'block' : 'none';
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
            programarDisponibilidad();
        });
        document.getElementById('espacios-por-lugar').addEventListener('change', function () {
            actualizarCapacidad();
            programarDisponibilidad();
        });
        document.getElementById('horarios-container').addEventListener('change', programarDisponibilidad);
        document.getElementById('cantPersoAtender').addEventListener('input', actualizarCapacidad);
    }

    function actualizarEspacios() {
        document.querySelectorAll('input[name="idLugarActividad[]"]').forEach(function (lugar) {
            const bloque = document.querySelector('.espacio-lugar[data-lugar="' + lugar.value + '"]');
            if (!bloque) return;
            const visible = lugar.checked && lugar.dataset.sede === '1';
            bloque.style.display = visible ? 'block' : 'none';
            const select = bloque.querySelector('select');
            select.disabled = !visible;
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

    function consultarDisponibilidad() {
        const inicio = valorCampo('fechainicioActividad');
        const fin = valorCampo('fechafinActividad');
        const lugares = obtenerSeleccionados('idLugarActividad[]');
        const horarios = obtenerSeleccionados('idHorario[]');

        if (!inicio || !fin || fin < inicio || !lugares.length) {
            marcarOcupados('idHorario[]', [], 'Horario ocupado en las fechas y lugares seleccionados');
            marcarOcupados('idDocente[]', [], 'Docente ocupado en una fecha y horario seleccionados');
            return;
        }

        const params = new URLSearchParams();
        params.set('action', 'obtenerHorariosOcupados');
        params.set('fechaInicio', inicio);
        params.set('fechaFin', fin);
        params.set('idExcluir', window.idActividadActual || '');
        fechasSesiones.forEach(function (fecha) { params.append('fechasSesiones[]', fecha); });
        lugares.forEach(function (id) { params.append('idLugares[]', id); });
        horarios.forEach(function (id) { params.append('idHorarios[]', id); });
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
                const quitoDocentes = marcarOcupados('idDocente[]', data.docentesOcupados || [], 'Docente ocupado en una fecha y horario seleccionados');
                if (quitoHorarios) {
                    mostrarAlerta('warning', 'Se quitaron horarios que ya están ocupados en las fechas o lugares seleccionados.');
                    programarDisponibilidad();
                }
                if (quitoDocentes) mostrarAlerta('warning', 'Se quitaron docentes que ya tienen una actividad en una fecha y horario seleccionados.');
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
    function inicializarModales() {
        document.getElementById('btnGuardarLugar').addEventListener('click', guardarNuevoLugar);
        document.getElementById('btnGuardarDocente').addEventListener('click', guardarNuevoDocente);
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
        if (!data.get('cedDocente').trim() || !data.get('nacionalidad') || !data.get('nombreDocente').trim() || !data.get('apellidoDocente').trim()) {
            mostrarAlerta('danger', 'Cédula, nacionalidad, nombres y apellidos son obligatorios.');
            return;
        }

        enviarCatalogo('guardarDocenteAjax', data, document.getElementById('btnGuardarDocente'), function (respuesta) {
            const nombre = data.get('nombreDocente').trim() + ' ' + data.get('apellidoDocente').trim();
            agregarDocente(respuesta.id, nombre, data.get('cedDocente').trim());
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
        bloque.append(labelEspacio, select);
        document.getElementById('espacios-por-lugar').appendChild(bloque);
        actualizarEspacios();
        actualizarResumenSelectores();
        programarDisponibilidad();
        actualizarResumen();
    }

    function agregarDocente(id, nombre, cedula) {
        const etiqueta = crearOpcionSelector('idDocente[]', id, nombre, 'C.I. ' + cedula, 'bi-person-badge');
        const contenedor = document.getElementById('docentes-container');
        contenedor.appendChild(etiqueta);
        contenedor.closest('.selector-multiple').querySelector('[data-buscar]').dispatchEvent(new Event('input'));
        actualizarResumenSelectores();
        programarDisponibilidad();
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
            ['Sesiones', fechasSesiones.map(function (fecha, indice) { return 'S' + (indice + 1) + ': ' + formatearFecha(fecha); }).join(' | ')],
            ['Lugares', obtenerTextos('idLugarActividad[]').join(', ')],
            ['Espacios', Array.from(document.querySelectorAll('.espacio-select:not(:disabled)')).filter(function (select) { return select.value; }).map(function (select) { return select.options[select.selectedIndex].textContent; }).join(', ') || 'No aplica'],
            ['Horarios', obtenerTextos('idHorario[]').join(', ')],
            ['Personas', valorCampo('cantPersoAtender')],
            ['Responsables', obtenerTextos('idEmpleado[]').join(', ')],
            ['Docentes', obtenerTextos('idDocente[]').join(', ')],
            ['Tipo de entrega', textoSelect('idTipEntrega')],
            ['Observaciones', valorCampo('observacion') || 'Sin observaciones']
        ];
        document.getElementById('resumen-content').innerHTML = '<div class="resumen-section">' + filas.map(function (fila) {
            return '<div class="resumen-row"><span class="label">' + escapar(fila[0]) + ':</span><span class="value">' + escapar(fila[1] || '-') + '</span></div>';
        }).join('') + '</div>';
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
        const data = new FormData(document.getElementById('formActividad'));
        fechasSesiones.forEach(function (fecha) { data.append('fechasSesiones[]', fecha); });
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
