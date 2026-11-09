<?php
// Lectura de la ficha de seguimiento en Excel

class FichaSeguimientoExcel
{
    private $ruta;

    public function __construct($ruta)
    {
        $this->ruta = $ruta;
    }

    public function leer()
    {
        $zip = new ZipArchive();
        if ($zip->open($this->ruta) !== true) {
            throw new RuntimeException('El archivo no es un Excel válido.');
        }

        try {
            $hoja = $this->obtenerRutaHoja($zip, 'BEN-AE1');
            $compartidos = $this->obtenerTextosCompartidos($zip);
            $xml = $this->obtenerXml($zip, $hoja);
            return $this->obtenerFilas($xml, $compartidos);
        } finally {
            $zip->close();
        }
    }

    private function obtenerXml($zip, $ruta)
    {
        $contenido = $zip->getFromName($ruta);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el contenido del archivo Excel.');
        }
        $xml = @simplexml_load_string($contenido);
        if ($xml === false) {
            throw new RuntimeException('El contenido del archivo Excel está dañado.');
        }
        $xml->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        return $xml;
    }

    private function obtenerRutaHoja($zip, $nombreHoja)
    {
        $libro = $this->obtenerXml($zip, 'xl/workbook.xml');
        $libro->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $idRelacion = '';

        $hojas = $libro->xpath('//m:sheets/m:sheet');
        if ($hojas === false) {
            throw new RuntimeException('La estructura de hojas del archivo Excel está dañada.');
        }
        foreach ($hojas as $hoja) {
            if ((string) $hoja['name'] === $nombreHoja) {
                $atributos = $hoja->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $idRelacion = (string) $atributos['id'];
                break;
            }
        }
        if ($idRelacion === '') {
            throw new RuntimeException('El archivo debe conservar la hoja BEN-AE1 de la ficha original.');
        }

        $contenidoRelaciones = $zip->getFromName('xl/_rels/workbook.xml.rels');
        $relaciones = $contenidoRelaciones === false ? false : @simplexml_load_string($contenidoRelaciones);
        if ($relaciones === false) {
            throw new RuntimeException('No se pudo identificar la hoja BEN-AE1 porque las relaciones del Excel están dañadas.');
        }
        foreach ($relaciones as $relacion) {
            if ((string) $relacion['Id'] === $idRelacion) {
                $destino = ltrim((string) $relacion['Target'], '/');
                return strpos($destino, 'xl/') === 0 ? $destino : 'xl/' . $destino;
            }
        }
        throw new RuntimeException('No se pudo identificar la hoja BEN-AE1.');
    }

    private function obtenerTextosCompartidos($zip)
    {
        $contenido = $zip->getFromName('xl/sharedStrings.xml');
        if ($contenido === false) {
            return [];
        }
        $xml = $this->obtenerXml($zip, 'xl/sharedStrings.xml');
        $textos = [];
        $elementos = $xml->xpath('//m:si');
        if ($elementos === false) {
            throw new RuntimeException('Los textos internos del archivo Excel están dañados.');
        }
        foreach ($elementos as $elemento) {
            $elemento->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $texto = '';
            $partes = $elemento->xpath('.//m:t');
            if ($partes === false) {
                throw new RuntimeException('Uno de los textos del archivo Excel no se pudo leer.');
            }
            foreach ($partes as $parte) {
                $texto .= (string) $parte;
            }
            $textos[] = $texto;
        }
        return $textos;
    }

    private function obtenerFilas($xml, $compartidos)
    {
        $filas = [];
        $filasXml = $xml->xpath('//m:sheetData/m:row');
        if ($filasXml === false) {
            throw new RuntimeException('La hoja BEN-AE1 no tiene una estructura de filas válida.');
        }
        foreach ($filasXml as $filaXml) {
            $numero = (int) $filaXml['r'];
            if ($numero < 9) {
                continue;
            }

            $valores = array_fill_keys(['A', 'B', 'C', 'D', 'E', 'F', 'G'], '');
            foreach ($filaXml->c as $celda) {
                $columna = preg_replace('/\d+/', '', (string) $celda['r']);
                if (!array_key_exists($columna, $valores)) {
                    continue;
                }
                $valores[$columna] = $this->valorCelda($celda, $compartidos);
            }

            if (implode('', $valores) === '') {
                continue;
            }
            if (count($filas) >= 1000) {
                throw new RuntimeException('La ficha no puede contener más de 1000 beneficiarios por importación.');
            }
            $filas[] = [
                'fila' => $numero,
                'nombreCompleto' => trim($valores['A']),
                'fechaNac' => trim($valores['B']),
                'cedula' => trim($valores['C']),
                'fechaActividad' => trim($valores['D']),
                'nroActividad' => trim($valores['E']),
                'sexo' => trim($valores['G'])
            ];
        }
        return $filas;
    }

    private function valorCelda($celda, $compartidos)
    {
        $tipo = (string) $celda['t'];
        if ($tipo === 'inlineStr') {
            $celda->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $partes = $celda->xpath('.//m:is//m:t');
            if ($partes === false) {
                throw new RuntimeException('No se pudo leer una celda de texto de la ficha.');
            }
            $texto = '';
            foreach ($partes as $parte) {
                $texto .= (string) $parte;
            }
            return $texto;
        }

        $valor = isset($celda->v) ? (string) $celda->v : '';
        if ($tipo === 's' && $valor !== '') {
            $indice = (int) $valor;
            return $compartidos[$indice] ?? '';
        }
        return $valor;
    }
}
