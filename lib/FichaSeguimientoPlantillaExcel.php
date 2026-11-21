<?php
// Formato de importación con los datos de la sesión

class FichaSeguimientoPlantillaExcel
{
    private $rutaPlantilla;

    public function __construct($rutaPlantilla)
    {
        $this->rutaPlantilla = $rutaPlantilla;
    }

    public function generar($nroSesion, $fechaSesion)
    {
        if (!class_exists('ZipArchive') || !class_exists('DOMDocument')) {
            throw new RuntimeException('El servidor necesita las extensiones ZIP y XML de PHP para generar el formato.');
        }
        if (!is_file($this->rutaPlantilla)) {
            throw new RuntimeException('No se encontró la plantilla Ficha de Seguimiento.xlsx.');
        }

        $nroSesion = (int) $nroSesion;
        $fecha = DateTime::createFromFormat('!Y-m-d', $fechaSesion);
        if ($nroSesion < 1 || !$fecha || $fecha->format('Y-m-d') !== $fechaSesion) {
            throw new RuntimeException('Los datos de la sesión no son válidos.');
        }

        $tiempo = gmmktime(12, 0, 0, (int) $fecha->format('m'), (int) $fecha->format('d'), (int) $fecha->format('Y'));
        $fechaExcel = (int) ($tiempo / 86400 + 25569);

        $origen = new ZipArchive();
        if ($origen->open($this->rutaPlantilla, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('No se pudo abrir la plantilla de seguimiento.');
        }

        $rutaHoja = $this->rutaHojaBeneficiarios($origen);
        $temporal = tempnam(sys_get_temp_dir(), 'formato_seguimiento_');
        $salida = new ZipArchive();
        if ($salida->open($temporal, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $origen->close();
            throw new RuntimeException('No se pudo crear el formato Excel.');
        }

        for ($i = 0; $i < $origen->numFiles; $i++) {
            $nombre = $origen->getNameIndex($i);
            $contenido = $origen->getFromIndex($i);
            if ($nombre === $rutaHoja) {
                $contenido = $this->asignarSesion($contenido, $nroSesion, $fechaExcel);
            }
            $salida->addFromString($nombre, $contenido);
        }

        $origen->close();
        $salida->close();
        $contenido = file_get_contents($temporal);
        unlink($temporal);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el formato generado.');
        }
        return $contenido;
    }

    private function rutaHojaBeneficiarios($zip)
    {
        $libro = new DOMDocument();
        if (!$libro->loadXML($zip->getFromName('xl/workbook.xml'), LIBXML_NONET)) {
            throw new RuntimeException('La estructura del libro Excel está dañada.');
        }
        $xp = new DOMXPath($libro);
        $xp->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xp->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $hoja = $xp->query('//x:sheets/x:sheet[@name="BENEFICIARIOS"]')->item(0);
        if (!$hoja) {
            throw new RuntimeException('La plantilla no contiene la hoja BENEFICIARIOS.');
        }
        $idRelacion = $hoja->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');

        $relaciones = new DOMDocument();
        if (!$relaciones->loadXML($zip->getFromName('xl/_rels/workbook.xml.rels'), LIBXML_NONET)) {
            throw new RuntimeException('Las relaciones del libro Excel están dañadas.');
        }
        $xpRel = new DOMXPath($relaciones);
        $xpRel->registerNamespace('pr', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $relacion = $xpRel->query('//pr:Relationship[@Id="' . $idRelacion . '"]')->item(0);
        if (!$relacion) {
            throw new RuntimeException('No se pudo localizar la hoja BENEFICIARIOS.');
        }

        $destino = ltrim($relacion->getAttribute('Target'), '/');
        return strpos($destino, 'xl/') === 0 ? $destino : 'xl/' . $destino;
    }

    private function asignarSesion($xml, $nroSesion, $fechaExcel)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        if (!$dom->loadXML($xml, LIBXML_NONET)) {
            throw new RuntimeException('La hoja BENEFICIARIOS está dañada.');
        }
        $xp = new DOMXPath($dom);
        $xp->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        foreach ($xp->query('//x:sheetData/x:row/x:c') as $celda) {
            $referencia = $celda->getAttribute('r');
            if (!preg_match('/^([A-Z]+)(\d+)$/', $referencia, $partes)) continue;
            $columna = $partes[1];
            $fila = (int) $partes[2];
            if ($fila < 6 || $fila > 1008) continue;

            $limpiar = in_array($columna, ['A', 'B', 'C', 'F', 'G'], true);
            $esNumero = $columna === 'E';
            $esFecha = $columna === 'D';
            if (!$limpiar && !$esNumero && !$esFecha) continue;

            $celda->removeAttribute('t');
            while ($celda->firstChild) {
                $celda->removeChild($celda->firstChild);
            }
            if ($limpiar) continue;

            if ($esFecha) {
                $fechaTexto = gmdate('Y-m-d', ($fechaExcel - 25569) * 86400);
                $celda->setAttribute('t', 'inlineStr');
                $contenido = $dom->createElementNS($celda->namespaceURI, 'is');
                $contenido->appendChild($dom->createElementNS($celda->namespaceURI, 't', $fechaTexto));
                $celda->appendChild($contenido);
                continue;
            }

            $celda->appendChild($dom->createElementNS($celda->namespaceURI, 'v', (string) $nroSesion));
        }

        return $dom->saveXML();
    }
}
