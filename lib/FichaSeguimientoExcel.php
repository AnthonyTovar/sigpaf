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
        $xml = simplexml_load_string($contenido);
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

        foreach ($libro->xpath('//m:sheets/m:sheet') as $hoja) {
            if ((string) $hoja['name'] === $nombreHoja) {
                $atributos = $hoja->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $idRelacion = (string) $atributos['id'];
                break;
            }
        }
        if ($idRelacion === '') {
            throw new RuntimeException('El archivo debe conservar la hoja BEN-AE1 de la ficha original.');
        }

        $relaciones = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        if ($relaciones === false) {
            throw new RuntimeException('No se pudo identificar la hoja BEN-AE1.');
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
        foreach ($xml->xpath('//m:si') as $elemento) {
            $elemento->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $texto = '';
            foreach ($elemento->xpath('.//m:t') as $parte) {
                $texto .= (string) $parte;
            }
            $textos[] = $texto;
        }
        return $textos;
    }

    private function obtenerFilas($xml, $compartidos)
    {
        $filas = [];
        foreach ($xml->xpath('//m:sheetData/m:row') as $filaXml) {
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
            $texto = '';
            foreach ($celda->is->t as $parte) {
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
