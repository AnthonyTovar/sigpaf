// Buscador y selección de beneficiarios
(function () {
    'use strict';

    var buscador = document.getElementById('buscadorBeneficiarios');
    if (!buscador) {
        return;
    }

    var filas = Array.prototype.slice.call(document.querySelectorAll('.fila-beneficiario'));
    var sinResultados = document.getElementById('sinResultadosBeneficiarios');
    var totalVisibles = document.getElementById('beneficiariosVisibles');
    var totalSeleccionados = document.getElementById('beneficiariosSeleccionados');
    var textoExportacion = document.getElementById('textoExportacionBeneficiarios');
    var seleccionarVisibles = document.getElementById('seleccionarBeneficiariosVisibles');
    var limpiarSeleccion = document.getElementById('limpiarSeleccionBeneficiarios');
    var limpiarBusqueda = document.getElementById('limpiarBusquedaBeneficiarios');

    function normalizar(texto) {
        return (texto || '')
            .toLocaleLowerCase('es')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function casillasSeleccionadas() {
        return filas.map(function (fila) {
            return fila.querySelector('.check-beneficiario');
        }).filter(function (casilla) {
            return casilla && casilla.checked;
        });
    }

    function actualizarSeleccion() {
        var seleccionados = casillasSeleccionadas().length;
        filas.forEach(function (fila) {
            var casilla = fila.querySelector('.check-beneficiario');
            fila.classList.toggle('seleccionado', Boolean(casilla && casilla.checked));
        });

        if (totalSeleccionados) {
            totalSeleccionados.textContent = seleccionados + ' seleccionado(s)';
        }
        if (textoExportacion) {
            textoExportacion.textContent = seleccionados > 0
                ? 'Se exportarán únicamente los ' + seleccionados + ' beneficiarios seleccionados.'
                : 'Sin selección: se exportarán todos los beneficiarios registrados.';
        }
        if (limpiarSeleccion) {
            limpiarSeleccion.classList.toggle('visible', seleccionados > 0);
        }

        if (seleccionarVisibles) {
            var visibles = filas.filter(function (fila) {
                return !fila.hidden;
            });
            var marcados = visibles.filter(function (fila) {
                var casilla = fila.querySelector('.check-beneficiario');
                return casilla && casilla.checked;
            }).length;
            seleccionarVisibles.checked = visibles.length > 0 && marcados === visibles.length;
            seleccionarVisibles.indeterminate = marcados > 0 && marcados < visibles.length;
            seleccionarVisibles.disabled = visibles.length === 0;
        }
    }

    function filtrar() {
        var termino = normalizar(buscador.value);
        var visibles = 0;
        filas.forEach(function (fila) {
            var coincide = termino === '' || normalizar(fila.getAttribute('data-busqueda')).indexOf(termino) !== -1;
            fila.hidden = !coincide;
            if (coincide) {
                visibles++;
            }
        });
        totalVisibles.textContent = visibles + ' de ' + filas.length + ' beneficiario(s)';
        sinResultados.hidden = visibles !== 0;
        limpiarBusqueda.classList.toggle('visible', buscador.value !== '');
        actualizarSeleccion();
    }

    buscador.addEventListener('input', filtrar);

    limpiarBusqueda.addEventListener('click', function () {
        buscador.value = '';
        filtrar();
        buscador.focus();
    });

    filas.forEach(function (fila) {
        var casilla = fila.querySelector('.check-beneficiario');
        if (casilla) {
            casilla.addEventListener('change', actualizarSeleccion);
        }
    });

    if (seleccionarVisibles) {
        seleccionarVisibles.addEventListener('change', function () {
            filas.forEach(function (fila) {
                var casilla = fila.querySelector('.check-beneficiario');
                if (!fila.hidden && casilla) {
                    casilla.checked = seleccionarVisibles.checked;
                }
            });
            actualizarSeleccion();
        });
    }

    if (limpiarSeleccion) {
        limpiarSeleccion.addEventListener('click', function () {
            filas.forEach(function (fila) {
                var casilla = fila.querySelector('.check-beneficiario');
                if (casilla) {
                    casilla.checked = false;
                }
            });
            actualizarSeleccion();
        });
    }

    filtrar();
}());
