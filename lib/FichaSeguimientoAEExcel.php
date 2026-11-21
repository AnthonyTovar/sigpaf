<?php
// Exportación de la ficha de seguimiento por área específica

class FichaSeguimientoAEExcel
{
    private $rutaPlantilla;
    private $hojas = ['AE1', 'AE2', 'AE3', 'AE4', 'AE5', 'GESTION INSTITUCIONAL'];
    private $celdasHoja = [];
    private $filasHoja = [];

    public function __construct($rutaPlantilla)
    {
        $this->rutaPlantilla = $rutaPlantilla;
    }

    public function generar($actividades)
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('El servidor necesita la extensión ZIP de PHP para exportar la ficha.');
        }
        if (!class_exists('DOMDocument')) {
            throw new RuntimeException('El servidor necesita la extensión XML de PHP para exportar la ficha.');
        }
        if (!is_file($this->rutaPlantilla)) {
            throw new RuntimeException('No se encontró la plantilla Ficha SeguimientoAE.xlsx.');
        }

        $filas = array_fill_keys($this->hojas, []);
        foreach ($actividades as $actividad) {
            $hoja = $this->hojaDestino($actividad);
            if ($hoja === '') {
                throw new RuntimeException('El área "' . ($actividad['nomAreaE'] ?? $actividad['idAreaE']) .
                    '" no tiene una hoja asignada en la plantilla.');
            }
            foreach ($this->filasActividad($actividad) as $fila) {
                $filas[$hoja][] = $fila;
            }
        }

        foreach ($filas as &$lista) {
            usort($lista, function ($a, $b) {
                $fecha = strcmp($a['_fecha'], $b['_fecha']);
                return $fecha !== 0 ? $fecha : strcmp($a['_actividad'], $b['_actividad']);
            });
        }
        unset($lista);

        $origen = new ZipArchive();
        if ($origen->open($this->rutaPlantilla, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('No se pudo abrir la plantilla de seguimiento.');
        }

        $rutasHojas = $this->rutasHojas($origen);
        $temporal = tempnam(sys_get_temp_dir(), 'ficha_ae_');
        $salida = new ZipArchive();
        if ($salida->open($temporal, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $origen->close();
            throw new RuntimeException('No se pudo crear el archivo Excel de salida.');
        }

        for ($i = 0; $i < $origen->numFiles; $i++) {
            $nombre = $origen->getNameIndex($i);
            $contenido = $origen->getFromIndex($i);
            $hoja = array_search($nombre, $rutasHojas, true);
            if ($hoja !== false) {
                $contenido = $this->llenarHoja($contenido, $filas[$hoja], $hoja !== 'GESTION INSTITUCIONAL');
            } elseif ($nombre === 'xl/workbook.xml') {
                $contenido = $this->forzarRecalculo($contenido);
            }
            $salida->addFromString($nombre, $contenido);
        }

        $origen->close();
        $salida->close();
        $contenido = file_get_contents($temporal);
        unlink($temporal);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el archivo Excel generado.');
        }

        return ['contenido' => $contenido, 'filas' => array_map('count', $filas)];
    }

    private function hojaDestino($actividad)
    {
        $area = mb_strtoupper(trim((string) ($actividad['nomAreaE'] ?? '')));
        if (preg_match('/\bAE\s*([1-5])\b/', $area, $coincide)) {
            return 'AE' . $coincide[1];
        }
        if (($actividad['idAreaE'] ?? '') === 'AE0006' ||
            (strpos($area, 'GESTION') !== false && strpos($area, 'INSTITUCIONAL') !== false)) {
            return 'GESTION INSTITUCIONAL';
        }
        return '';
    }

    private function filasActividad($actividad)
    {
        $sesiones = $actividad['sesiones'] ?? [];
        if (empty($sesiones)) {
            $sesiones = [[
                'idSegActividad' => '',
                'nroSesionPlanificada' => 1,
                'fechaSesion' => $actividad['fechainicioActividad'],
                'logroActividad' => '',
                'observObstaculo' => '',
                'nomTipEntrega' => '',
                'docentes' => [],
                'asistentes' => []
            ]];
        }

        $fecha = $actividad['fechainicioActividad'] ?: ($sesiones[0]['fechaSesion'] ?? '');
        $asistentes = [];
        $logros = [];
        $obstaculos = [];
        $tiposEntrega = [];
        $docentes = [];
        $ubicaciones = ['municipios' => [], 'parroquias' => [], 'lugares' => []];
        $registrada = false;

        foreach ($sesiones as $sesion) {
            $numeroSesion = (int) ($sesion['nroSesionPlanificada'] ?? 1);
            $fechaSesion = $sesion['fechaSesion'] ?? '';
            $etiqueta = 'Sesión ' . $numeroSesion .
                ($fechaSesion ? ' (' . date('d/m/Y', strtotime($fechaSesion)) . ')' : '');
            $logro = trim((string) ($sesion['logroActividad'] ?? ''));
            $obstaculo = trim((string) ($sesion['observObstaculo'] ?? ''));
            if ($logro !== '') {
                $logros[] = $etiqueta . ': ' . $logro;
                $registrada = true;
            }
            if ($obstaculo !== '') $obstaculos[] = $etiqueta . ': ' . $obstaculo;
            if (!empty($sesion['nomTipEntrega'])) $tiposEntrega[] = $sesion['nomTipEntrega'];

            foreach ($sesion['asistentes'] ?? [] as $beneficiario) {
                $clave = $beneficiario['idBeneficiario'] ?? $beneficiario['cedulaBeneficiario'] ?? '';
                if ($clave === '') $clave = md5(serialize($beneficiario));
                $asistentes[$clave] = $beneficiario;
            }
            if (!empty($sesion['asistentes'])) $registrada = true;

            $docentesSesion = !empty($sesion['docentes']) ? $sesion['docentes'] : ($actividad['docentes'] ?? []);
            foreach ($docentesSesion as $docente) {
                $clave = $docente['idDocente'] ?? md5(serialize($docente));
                $docentes[$clave] = $docente;
            }

            $ubicacionSesion = $this->ubicacionSesion($actividad, $sesion);
            foreach ($ubicaciones as $campo => $valores) {
                $ubicaciones[$campo] = array_merge($valores, explode("\n", $ubicacionSesion[$campo]));
            }
        }
        unset($valores);

        if (trim((string) ($actividad['observacion'] ?? '')) !== '') {
            $obstaculos[] = $actividad['observacion'];
        }
        foreach ($ubicaciones as $campo => $valores) {
            $ubicaciones[$campo] = $this->unirUnicos($valores);
        }

        $responsables = $this->responsables($actividad);
        $personas = $this->personas($responsables['empleados'], array_values($docentes));
        $conteos = $this->contarBeneficiarios(array_values($asistentes), $fecha);

        return [[
            '_fecha' => $fecha,
            '_actividad' => $actividad['idActividad'],
            'C' => ['fecha', $fecha],
            'D' => $actividad['nomTipoActividad'] ?? '',
            'E' => '',
            'F' => $actividad['nombreActividad'] ?? '',
            'G' => $actividad['descActividad'] ?? '',
            'H' => $actividad['objetivoActividad'] ?? '',
            'I' => $responsables['unidades'],
            'J' => $personas['nombres'],
            'K' => $personas['telefonos'],
            'L' => $personas['correos'],
            'M' => $ubicaciones['municipios'],
            'N' => $ubicaciones['parroquias'],
            'O' => $ubicaciones['lugares'],
            'P' => '',
            'Q' => $this->unirUnicos($tiposEntrega),
            'R' => $actividad['nomUnidadMedida'] ?? '',
            'S' => $registrada ? 1 : '',
            'T' => implode("\n\n", $logros),
            'U' => $conteos['U'] ?: '',
            'V' => $conteos['V'],
            'W' => $conteos['W'] ?: '',
            'X' => $conteos['X'],
            'Y' => $conteos['Y'] ?: '',
            'Z' => $conteos['Z'],
            'AA' => $conteos['AA'] ?: '',
            'AB' => $conteos['AB'],
            'AD' => '',
            'AE' => $this->perteneceEtnia($actividad['nomGrupoEtnio'] ?? '', 'INDIGENA'),
            'AF' => $this->perteneceEtnia($actividad['nomGrupoEtnio'] ?? '', 'AFRODESCENDIENTE'),
            'AG' => implode("\n", $obstaculos),
            'AH' => $actividad['nomEstDesarrollo'] ?? ''
        ]];
    }

    private function responsables($actividad)
    {
        $empleados = $actividad['empleados'] ?? [];
        $unidades = [];
        foreach ($empleados as $empleado) {
            if (!empty($empleado['nomUnidadEjecutora'])) {
                $unidades[] = $empleado['nomUnidadEjecutora'];
            }
        }
        return ['empleados' => $empleados, 'unidades' => $this->unirUnicos($unidades)];
    }

    private function personas($empleados, $docentes)
    {
        $nombres = [];
        $telefonos = [];
        $correos = [];
        foreach ($empleados as $empleado) {
            $nombres[] = trim(($empleado['nombres'] ?? '') . ' ' . ($empleado['apellidos'] ?? ''));
            $telefonos[] = $empleado['telefonoEmpleado'] ?? '';
            $correos[] = $empleado['correoEmpleado'] ?? '';
        }
        foreach ($docentes as $docente) {
            $nombre = trim(($docente['nombreDocente'] ?? '') . ' ' . ($docente['apellidoDocente'] ?? ''));
            if (($docente['idDocente'] ?? '') === 'DC0001' || mb_strtoupper($nombre) === 'NO APLICA') continue;
            $nombres[] = $nombre;
            $telefonos[] = $docente['telfDocente'] ?? '';
        }
        return [
            'nombres' => $this->unirUnicos($nombres),
            'telefonos' => $this->unirUnicos($telefonos),
            'correos' => $this->unirUnicos($correos)
        ];
    }

    private function ubicacionSesion($actividad, $sesion)
    {
        if (!empty($sesion['lugarSesion'])) {
            return [
                'municipios' => $sesion['municipioSesion'] ?? '',
                'parroquias' => $sesion['parroquiaSesion'] ?? '',
                'lugares' => $sesion['lugarSesion']
            ];
        }
        $municipios = [];
        $parroquias = [];
        $lugares = [];
        foreach ($actividad['lugares'] ?? [] as $lugar) {
            $municipios[] = $lugar['nombreMunicipio'] ?? '';
            $parroquias[] = $lugar['nombreParroquia'] ?? '';
            $lugares[] = $lugar['nomLugarActividad'] ?? '';
        }
        return [
            'municipios' => $this->unirUnicos($municipios),
            'parroquias' => $this->unirUnicos($parroquias),
            'lugares' => $this->unirUnicos($lugares)
        ];
    }

    private function contarBeneficiarios($beneficiarios, $fecha)
    {
        $conteos = ['U' => 0, 'W' => 0, 'Y' => 0, 'AA' => 0];
        $grupos = ['V' => [], 'X' => [], 'Z' => [], 'AB' => []];
        foreach ($beneficiarios as $beneficiario) {
            $edad = $this->edad($beneficiario['fechaNacBeneficiario'] ?? '', $fecha);
            $sexo = strtoupper((string) ($beneficiario['sexoBeneficiario'] ?? ''));
            if ($edad === null || !in_array($sexo, ['M', 'F'], true)) {
                continue;
            }
            if ($sexo === 'F' && $edad <= 11) {
                $columna = 'U';
                $grupo = 'V';
            } elseif ($sexo === 'M' && $edad <= 11) {
                $columna = 'W';
                $grupo = 'X';
            } elseif ($sexo === 'F') {
                $columna = 'Y';
                $grupo = 'Z';
            } else {
                $columna = 'AA';
                $grupo = 'AB';
            }
            $conteos[$columna]++;
            $grupos[$grupo][] = $this->grupoEdad($edad);
        }
        foreach ($grupos as $columna => $valores) {
            $conteos[$columna] = $this->unirUnicos($valores);
        }
        return $conteos;
    }

    private function edad($fechaNacimiento, $fechaReferencia)
    {
        $nacimiento = DateTime::createFromFormat('!Y-m-d', $fechaNacimiento);
        $referencia = DateTime::createFromFormat('!Y-m-d', $fechaReferencia);
        if (!$nacimiento || !$referencia || $nacimiento > $referencia) {
            return null;
        }
        return (int) $nacimiento->diff($referencia)->y;
    }

    private function grupoEdad($edad)
    {
        if ($edad >= 60) return 'De 60 a más';
        $limites = [[0, 5], [6, 11], [12, 17], [18, 23], [24, 29], [30, 35],
            [36, 41], [42, 47], [48, 53], [54, 59]];
        foreach ($limites as $rango) {
            if ($edad >= $rango[0] && $edad <= $rango[1]) {
                return 'De ' . $rango[0] . ' a ' . $rango[1] . ' años';
            }
        }
        return '';
    }

    private function perteneceEtnia($grupo, $buscado)
    {
        $grupo = mb_strtoupper(trim($grupo));
        $grupo = strtr($grupo, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);
        if ($grupo === '') return '';
        if (strpos($grupo, $buscado) !== false) return 'SI';
        if (strpos($grupo, 'NO APLICA') !== false || strpos($grupo, 'NINGUNO') !== false ||
            strpos($grupo, 'INDIGENA') !== false || strpos($grupo, 'AFRODESCENDIENTE') !== false) {
            return 'NO';
        }
        return '';
    }

    private function unirUnicos($valores)
    {
        $unicos = [];
        foreach ($valores as $valor) {
            $valor = trim((string) $valor);
            if ($valor !== '') $unicos[$valor] = true;
        }
        return implode("\n", array_keys($unicos));
    }

    private function rutasHojas($zip)
    {
        $workbook = new DOMDocument();
        $workbook->loadXML($zip->getFromName('xl/workbook.xml'), LIBXML_NONET);
        $rels = new DOMDocument();
        $rels->loadXML($zip->getFromName('xl/_rels/workbook.xml.rels'), LIBXML_NONET);
        $xpRels = new DOMXPath($rels);
        $xpRels->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $destinos = [];
        foreach ($xpRels->query('//r:Relationship') as $rel) {
            $destinos[$rel->getAttribute('Id')] = 'xl/' . ltrim($rel->getAttribute('Target'), '/');
        }

        $xp = new DOMXPath($workbook);
        $xp->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xp->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $rutas = [];
        foreach ($xp->query('//x:sheets/x:sheet') as $sheet) {
            $nombre = $sheet->getAttribute('name');
            if (in_array($nombre, $this->hojas, true)) {
                $id = $sheet->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
                $rutas[$nombre] = $destinos[$id];
            }
        }
        return $rutas;
    }

    private function llenarHoja($xml, $filas, $esArea)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xml, LIBXML_NONET);
        $xp = new DOMXPath($dom);
        $xp->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $maximaFila = 10;
        $this->celdasHoja = [];
        $this->filasHoja = [];
        foreach ($xp->query('//x:sheetData/x:row') as $row) {
            $numero = (int) $row->getAttribute('r');
            $this->filasHoja[$numero] = $row;
            foreach ($row->childNodes as $cell) {
                if ($cell->nodeType !== XML_ELEMENT_NODE || $cell->localName !== 'c') continue;
                $this->celdasHoja[$cell->getAttribute('r')] = $cell;
            }
            if ($numero < 11) continue;
            $maximaFila = max($maximaFila, $numero);
            foreach ($row->childNodes as $cell) {
                if ($cell->nodeType !== XML_ELEMENT_NODE || $cell->localName !== 'c') continue;
                if ($this->numeroColumna($cell->getAttribute('r')) <= 51) {
                    $this->vaciarCelda($cell);
                }
            }
            $this->formula($dom, $xp, 'B' . $numero, 'IF(C' . $numero . '="","",TEXT(C' . $numero . ',"mmmm"))', '');
            $this->formula($dom, $xp, 'AC' . $numero,
                'IF(A' . $numero . '="","",SUM(U' . $numero . '+W' . $numero . '+Y' . $numero . '+AA' . $numero . '))', '');
        }

        if (count($filas) > $maximaFila - 10) {
            throw new RuntimeException('La cantidad de sesiones supera el espacio disponible en una hoja de la plantilla.');
        }
        if ($esArea) {
            foreach (['E4', 'E5', 'E6'] as $referencia) {
                $this->escribir($dom, $xp, $referencia, '');
            }
        }

        $numero = 11;
        foreach ($filas as $fila) {
            $this->escribir($dom, $xp, 'A' . $numero, $numero - 10);
            $mes = '';
            $total = 0;
            foreach ($fila as $columna => $valor) {
                if (strpos($columna, '_') === 0) continue;
                if (is_array($valor) && $valor[0] === 'fecha') {
                    $this->fecha($dom, $xp, $columna . $numero, $valor[1]);
                    $mes = $this->mes($valor[1]);
                } else {
                    $this->escribir($dom, $xp, $columna . $numero, $valor);
                }
                if (in_array($columna, ['U', 'W', 'Y', 'AA'], true)) {
                    $total += (int) $valor;
                }
            }
            $this->formula($dom, $xp, 'B' . $numero, 'IF(C' . $numero . '="","",TEXT(C' . $numero . ',"mmmm"))', $mes);
            $this->formula($dom, $xp, 'AC' . $numero,
                'IF(A' . $numero . '="","",SUM(U' . $numero . '+W' . $numero . '+Y' . $numero . '+AA' . $numero . '))', (string) $total);
            $numero++;
        }
        return $dom->saveXML();
    }

    private function celda($dom, $xp, $referencia)
    {
        if (isset($this->celdasHoja[$referencia])) return $this->celdasHoja[$referencia];

        preg_match('/([A-Z]+)(\d+)/', $referencia, $partes);
        $row = $this->filasHoja[(int) $partes[2]] ?? null;
        if (!$row) throw new RuntimeException('La plantilla no contiene la fila ' . $partes[2] . '.');
        $cell = $dom->createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'c');
        $cell->setAttribute('r', $referencia);
        $insertada = false;
        foreach ($row->childNodes as $existente) {
            if ($existente->nodeType === XML_ELEMENT_NODE && $existente->localName === 'c' &&
                $this->numeroColumna($existente->getAttribute('r')) > $this->numeroColumna($referencia)) {
                $row->insertBefore($cell, $existente);
                $insertada = true;
                break;
            }
        }
        if (!$insertada) $row->appendChild($cell);
        $this->celdasHoja[$referencia] = $cell;
        return $cell;
    }

    private function vaciarCelda($cell)
    {
        while ($cell->firstChild) $cell->removeChild($cell->firstChild);
        $cell->removeAttribute('t');
    }

    private function escribir($dom, $xp, $referencia, $valor)
    {
        $cell = $this->celda($dom, $xp, $referencia);
        $this->vaciarCelda($cell);
        if ($valor === '' || $valor === null) return;
        if (is_int($valor) || is_float($valor)) {
            $cell->appendChild($dom->createElementNS($cell->namespaceURI, 'v', (string) $valor));
            return;
        }
        $cell->setAttribute('t', 'inlineStr');
        $is = $dom->createElementNS($cell->namespaceURI, 'is');
        $texto = $dom->createElementNS($cell->namespaceURI, 't');
        $texto->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        $texto->appendChild($dom->createTextNode($this->limpiarTexto($valor)));
        $is->appendChild($texto);
        $cell->appendChild($is);
    }

    private function formula($dom, $xp, $referencia, $formula, $cache)
    {
        $cell = $this->celda($dom, $xp, $referencia);
        $this->vaciarCelda($cell);
        $cell->appendChild($dom->createElementNS($cell->namespaceURI, 'f', $formula));
        if (!is_numeric($cache)) $cell->setAttribute('t', 'str');
        $cell->appendChild($dom->createElementNS($cell->namespaceURI, 'v', $this->limpiarTexto($cache)));
    }

    private function fecha($dom, $xp, $referencia, $fecha)
    {
        $cell = $this->celda($dom, $xp, $referencia);
        $this->vaciarCelda($cell);
        if (!$fecha) return;
        $tiempo = gmmktime(12, 0, 0, (int) substr($fecha, 5, 2), (int) substr($fecha, 8, 2), (int) substr($fecha, 0, 4));
        $serial = $tiempo / 86400 + 25569;
        $cell->appendChild($dom->createElementNS($cell->namespaceURI, 'v', (string) $serial));
    }

    private function mes($fecha)
    {
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return $meses[(int) substr($fecha, 5, 2) - 1] ?? '';
    }

    private function numeroColumna($referencia)
    {
        $letras = preg_replace('/[^A-Z]/', '', $referencia);
        $numero = 0;
        for ($i = 0; $i < strlen($letras); $i++) {
            $numero = $numero * 26 + ord($letras[$i]) - 64;
        }
        return $numero;
    }

    private function limpiarTexto($texto)
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $texto);
    }

    private function forzarRecalculo($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml, LIBXML_NONET);
        $xp = new DOMXPath($dom);
        $xp->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $calc = $xp->query('//x:calcPr')->item(0);
        if (!$calc) {
            $calc = $dom->createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'calcPr');
            $dom->documentElement->appendChild($calc);
        }
        $calc->setAttribute('fullCalcOnLoad', '1');
        $calc->setAttribute('forceFullCalc', '1');
        return $dom->saveXML();
    }
}
