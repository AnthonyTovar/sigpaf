// Filtros de tablas








$(document).ready(function () {
    var $input = $('#buscadorModulo');
    if (!$input.length) return;

    var $toolbar = $input.closest('.card-body');
    var $filas = $toolbar.find('table tbody tr');
    var $btnLimpiar = $('#btnLimpiarBusqueda');
    var $contador = $('#contadorFiltro');
    var $btnPdf = $('#btnExportarPdf');
    var urlPdfBase = $btnPdf.length ? $btnPdf.attr('href').split('&q=')[0] : '';
    var totalFilas = $filas.filter(function () {
        return $(this).find('td[colspan]').length === 0;
    }).length;

    function aplicarFiltro() {
        var q = $input.val().trim().toLowerCase();
        var visibles = 0;

        $filas.each(function () {
            var $fila = $(this);

            if ($fila.find('td[colspan]').length) {
                $fila.toggle(q === '');
                return;
            }
            var coincide = q === '' || $fila.text().toLowerCase().indexOf(q) !== -1;
            $fila.toggle(coincide);
            if (coincide) visibles++;
        });


        if ($contador.length) {
            if (q === '') {
                $contador.text('');
            } else if (visibles === 0) {
                $contador.html('<span class="text-danger fw-semibold">Sin resultados para &quot;' +
                    $('<span>').text($input.val().trim()).html() + '&quot;</span>');
            } else {
                $contador.html('<span class="text-muted">Mostrando <strong>' + visibles + '</strong> de ' + totalFilas + '</span>');
            }
        }


        $btnLimpiar.toggleClass('d-none', q === '');


        if ($btnPdf.length) {
            $btnPdf.attr('href', q === '' ? urlPdfBase : urlPdfBase + '&q=' + encodeURIComponent($input.val().trim()));
        }
    }

    $input.on('input', aplicarFiltro);
    $btnLimpiar.on('click', function () {
        $input.val('').trigger('input').focus();
    });
});
