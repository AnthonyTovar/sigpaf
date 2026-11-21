<?php
// Agenda Fundacite Yaracuy en Excel

class AgendaExcel
{
    private $instructivoTitulo = 'INSTRUCTIVO  PARA EL LLENADO DEL FORMATO AGENDA FUNDACITE';

    private $instructivoTexto = <<<'TEXTO'
1.- Lea con detenimiento las recomendaciones que se dan para el llenado del Formato.  

2.-El formato posee ocho hojas de cálculo, a saber: Planificada, Ejecutada, Semillero Científico Planificada, Semillero Científico Ejecutada, Cayapa Heroica Planificada, Cayapa Heroica Ejecutada, Alianza Científico Campesina Planificada y Alianza Científico Campesina Ejecutada.  

3.- Cada renglón de los estados posee cinco (05) espacios, por lo tanto es necesario que priorice solo cinco actividades planificadas o ejecutadas que sean significativamente de impacto. (POR CADA ESPACIO ES UNA (01) ACTIVIDAD) . 

4.- En la casilla agenda Planificada deben ir aquellas actividades de impacto que tenga programada su realización, desde el lunes hasta el viernes de la semana próxima inmediata, que no pertenezcan a los programas Semillero, Cayapa Heroica y Alianza Científico Campesina planificada respectivamente. 

5.-En las casilla Ejecutada, deben ir las actividades de impacto que hayan sido realizadas desde el lunes hasta el viernes de la semana en curso que no sean de los programas Semillero Científico, Cayapa Heroica y Alianza Científico Campesina ejecutadas respectivamente.  

6.- En la casilla Semillero Científico Planificada, deben colocarse las actividades de impacto que tenga programada su realización, desde el lunes hasta el viernes de la semana próxima. 

7.-En la Casilla Semillero Científico Ejecutada deben ir las actividades de impacto que hayan sido realizadas desde el lunes hasta el viernes de la semana en curso.  

8.- En la hoja Cayapa Heroica Planificada se colocan las actividades de impacto que tenga programada su realización, desde el lunes hasta el viernes de la semana próxima inmediata, también acá se colocan las relacionadas con el despistaje Neonatal que se hacen en algunas regiones.  

9.- En la hoja Cayapa Heroica Ejecutada se colocan las actividades de impacto que hayan sido realizadas desde el lunes hasta el viernes de la semana en curso, también acá se colocan las relacionadas con el despistaje Neonatal que se hacen en algunas regiones.  

10.- En la Casilla de Alianza Científica Planificada se colocan las actividades de impacto que tenga programada su realización, desde el lunes hasta el viernes de la semana próxima inmediata.  

11.-En la hoja de Alianza Científico campesina Ejecutada se colocan las actividades de impacto que hayan sido realizadas desde el lunes hasta el viernes de la semana en curso.  

12.- El tipo de letra utilizada es Arial de 11 pt, no letra mayúscula sostenida, no cursiva ni en resaltado en negrita. (Solo mayúscula el inicio del contenido, nombres, entre otros).  

13.- Se le agradece que tenga en cuenta hacer revisión ortográfica, de cohesión y coherencia antes de enviar el documento.  

14. La recepción de la agenda para la compilación por la Dirección General de la Oficina Territorial será los días lunes y martes, de cada semana, para poder hacer las posibles correcciones que sean necesarias el mismo día que sean recibidas las mismas.
TEXTO;

    private $hojas = [
        'INSTRUCTIVO AGENDA' => 'FFA6A6',
        'PLANIFICADA' => 'FFBF00',
        'EJECUTADA' => '2A6099',
        'SEMILLERO CIENTIFICO PLANIFICAD' => 'FF0000',
        'SEMILLERO CIENTIFICO EJECUTADO' => '158466',
        'CAYAPA HEROICA PLANIFICADA' => 'FFBF00',
        'CAYAPA HEROICA EJECUTADA' => '2A6099',
        'ALIANZA CIENTIFICA CAMPESINA PL' => '158466',
        'ALIANZA CIENTIFICA CAMPESINA EJ' => '158466',
    ];

    public function generar($actividades)
    {
        $filas = [];
        foreach ($this->hojas as $nombre => $color) {
            if ($nombre !== 'INSTRUCTIVO AGENDA') {
                $filas[$nombre] = [];
            }
        }

        foreach ($actividades as $a) {
            $destino = $this->destino($a);
            if ($destino !== null) {
                $filas[$destino][] = $this->fila($a);
            }
        }

        foreach ($filas as &$lista) {
            usort($lista, function ($x, $y) {
                return strcmp($x['orden'], $y['orden']);
            });
        }
        unset($lista);

        $tmp = tempnam(sys_get_temp_dir(), 'agenda');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->relsRaiz());
        $zip->addFromString('docProps/core.xml', $this->coreProps());
        $zip->addFromString('docProps/app.xml', $this->appProps());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());

        $i = 1;
        foreach ($this->hojas as $nombre => $color) {
            $datos = ($nombre === 'INSTRUCTIVO AGENDA') ? null : $filas[$nombre];
            $zip->addFromString('xl/worksheets/sheet' . $i . '.xml', $this->hoja($nombre, $color, $datos));
            $i++;
        }
        $zip->close();

        $contenido = file_get_contents($tmp);
        unlink($tmp);
        return $contenido;
    }

    private function destino($a)
    {
        $estatus = mb_strtolower(trim((string) ($a['nomEstatus'] ?? '')));
        if ($estatus === 'cancelada') {
            return null;
        }
        $ejecutada = ($estatus === 'ejecutada');

        $tipo = mb_strtolower((string) ($a['nomTipoActividad'] ?? ''));
        $vertice = (string) ($a['idVertice'] ?? '');
        $nomVertice = mb_strtolower((string) ($a['nombreVertice'] ?? ''));

        if (strpos($tipo, 'cayapa') !== false) {
            return $ejecutada ? 'CAYAPA HEROICA EJECUTADA' : 'CAYAPA HEROICA PLANIFICADA';
        }
        if ($vertice === 'VER0001' || strpos($nomVertice, 'siembra') !== false) {
            return $ejecutada ? 'SEMILLERO CIENTIFICO EJECUTADO' : 'SEMILLERO CIENTIFICO PLANIFICAD';
        }
        if ($vertice === 'VER0003' || strpos($nomVertice, 'producción') !== false || strpos($nomVertice, 'produccion') !== false) {
            return $ejecutada ? 'ALIANZA CIENTIFICA CAMPESINA EJ' : 'ALIANZA CIENTIFICA CAMPESINA PL';
        }
        return $ejecutada ? 'EJECUTADA' : 'PLANIFICADA';
    }

    private function fila($a)
    {
        $estados = [];
        $municipios = [];
        foreach ((array) ($a['ubicacion'] ?? []) as $u) {
            if (!empty($u['nombreEstado'])) {
                $estados[$u['nombreEstado']] = true;
            }
            if (!empty($u['nombreMunicipio'])) {
                $municipios[$u['nombreMunicipio']] = true;
            }
        }

        $lugares = [];
        foreach ((array) ($a['lugaresLista'] ?? []) as $l) {
            if (!empty($l['nomLugarActividad'])) {
                $lugares[] = $l['nomLugarActividad'];
            }
        }

        $horarios = [];
        foreach ((array) ($a['horariosLista'] ?? []) as $h) {
            if (!empty($h['nomHorario'])) {
                $horarios[] = $h['nomHorario'];
            }
        }

        $observaciones = '';
        $personas = (int) ($a['cantPersoAtender'] ?? 0);
        if ($personas > 0) {
            $observaciones = 'Se atenderán ' . $personas . ($personas === 1 ? ' persona.' : ' personas.');
        }
        $obs = trim((string) ($a['observacion'] ?? ''));
        if ($obs !== '') {
            $observaciones = trim($observaciones . ' ' . $obs);
        }

        $inicio = (string) ($a['fechainicioActividad'] ?? '');
        $fin = (string) ($a['fechafinActividad'] ?? '');

        return [
            'orden' => $inicio,
            'fecha' => $inicio,
            'fechaTexto' => ($fin !== '' && $fin !== $inicio)
                ? date('d/m/Y', strtotime($inicio)) . ' - ' . date('d/m/Y', strtotime($fin))
                : '',
            'actividad' => (string) ($a['nombreActividad'] ?? ''),
            'objetivo' => (string) ($a['objetivoActividad'] ?? ''),
            'descripcion' => (string) ($a['descActividad'] ?? ''),
            'estados' => $estados ? implode("\n", array_keys($estados)) : 'Yaracuy',
            'municipios' => implode("\n", array_keys($municipios)),
            'lugar' => implode("\n", $lugares),
            'hora' => implode("\n", $horarios),
            'observaciones' => $observaciones,
        ];
    }

    private function hoja($nombre, $color, $datos)
    {
        if ($datos === null) {
            return $this->hojaInstructivo($color);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetPr><tabColor rgb="FF' . $color . '"/></sheetPr>';
        $xml .= '<sheetViews><sheetView workbookViewId="0"/></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="12.75"/>';
        $xml .= '<cols>'
            . '<col min="1" max="1" width="18.5703125" customWidth="1"/>'
            . '<col min="2" max="2" width="19.28515625" customWidth="1"/>'
            . '<col min="3" max="3" width="38.42578125" customWidth="1"/>'
            . '<col min="5" max="5" width="60.28515625" customWidth="1"/>'
            . '<col min="6" max="6" width="29.140625" customWidth="1"/>'
            . '<col min="8" max="8" width="31.5703125" customWidth="1"/>'
            . '<col min="9" max="9" width="15.28515625" customWidth="1"/>'
            . '<col min="10" max="10" width="24" customWidth="1"/>'
            . '<col min="14" max="14" width="31.42578125" customWidth="1"/>'
            . '</cols>';

        $ente = ($nombre === 'PLANIFICADA') ? 'ENTE' : 'ENTES';

        $xml .= '<sheetData>';
        $xml .= '<row r="1" ht="45.75" customHeight="1"/>';

        $xml .= '<row r="2" ht="65.25" customHeight="1">';
        $xml .= $this->celdaTexto('A2', 1, $ente);
        $xml .= $this->celdaTexto('B2', 1, 'FECHA');
        $xml .= $this->celdaTexto('C2', 1, 'ACTIVIDADES');
        $xml .= $this->celdaTexto('D2', 1, 'OBJETIVOS');
        $xml .= $this->celdaTexto('E2', 1, 'BREVE DESCRIPCIÓN ');
        $xml .= $this->celdaTexto('F2', 1, 'ESTADOS');
        $xml .= $this->celdaTexto('G2', 1, 'MUNICIPIOS');
        $xml .= $this->celdaTexto('H2', 1, 'LUGAR');
        $xml .= $this->celdaTexto('I2', 1, 'HORA');
        $xml .= $this->celdaTexto('J2', 1, 'ENVERGADURA');
        $xml .= $this->celdaVacia('K2', 1);
        $xml .= $this->celdaVacia('L2', 1);
        $xml .= $this->celdaVacia('M2', 1);
        $xml .= $this->celdaTexto('N2', 1, 'OBSERVACIONES');
        $xml .= '</row>';

        $xml .= '<row r="3" ht="56.25" customHeight="1">';
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $col) {
            $xml .= $this->celdaVacia($col . '3', 1);
        }
        $xml .= $this->celdaTexto('J3', 1, 'MINISTERIAL');
        $xml .= $this->celdaTexto('K3', 1, 'VICEP. SECTORIAL');
        $xml .= $this->celdaTexto('L3', 1, 'VICEP. EJECUTIVA');
        $xml .= $this->celdaTexto('M3', 1, 'PRESIDENCIAL');
        $xml .= $this->celdaVacia('N3', 1);
        $xml .= '</row>';

        $r = 4;
        foreach ($datos as $f) {
            $altura = $this->alturaFila($f);
            $xml .= '<row r="' . $r . '" ht="' . $altura . '" customHeight="1">';
            $xml .= ($r === 4) ? $this->celdaTexto('A' . $r, 3, 'YARACUY') : $this->celdaVacia('A' . $r, 3);
            if ($f['fechaTexto'] !== '') {
                $xml .= $this->celdaTexto('B' . $r, 2, $f['fechaTexto']);
            } else {
                $xml .= $this->celdaFecha('B' . $r, 4, $f['fecha']);
            }
            $xml .= $this->celdaTexto('C' . $r, 2, $f['actividad']);
            $xml .= $this->celdaTexto('D' . $r, 2, $f['objetivo']);
            $xml .= $this->celdaTexto('E' . $r, 2, $f['descripcion']);
            $xml .= $this->celdaTexto('F' . $r, 2, $f['estados']);
            $xml .= $this->celdaTexto('G' . $r, 2, $f['municipios']);
            $xml .= $this->celdaTexto('H' . $r, 2, $f['lugar']);
            $xml .= $this->celdaTexto('I' . $r, 2, $f['hora']);
            $xml .= $this->celdaTexto('J' . $r, 2, 'X');
            $xml .= $this->celdaVacia('K' . $r, 2);
            $xml .= $this->celdaVacia('L' . $r, 2);
            $xml .= $this->celdaVacia('M' . $r, 2);
            $xml .= $this->celdaTexto('N' . $r, 2, $f['observaciones']);
            $xml .= '</row>';
            $r++;
        }
        $xml .= '</sheetData>';

        $merges = ['A2:A3', 'B2:B3', 'C2:C3', 'D2:D3', 'E2:E3', 'F2:F3', 'G2:G3', 'H2:H3', 'I2:I3', 'J2:M2', 'N2:N3'];
        if (count($datos) > 1) {
            $merges[] = 'A4:A' . (3 + count($datos));
        }
        $xml .= '<mergeCells count="' . count($merges) . '">';
        foreach ($merges as $m) {
            $xml .= '<mergeCell ref="' . $m . '"/>';
        }
        $xml .= '</mergeCells>';

        $xml .= '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>';
        $xml .= '</worksheet>';
        return $xml;
    }

    private function hojaInstructivo($color)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetPr><tabColor rgb="FF' . $color . '"/></sheetPr>';
        $xml .= '<sheetViews><sheetView workbookViewId="0"/></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="12.75"/>';
        $xml .= '<cols>'
            . '<col min="1" max="1" width="304.85546875" customWidth="1"/>'
            . '<col min="2" max="2" width="11.42578125" customWidth="1"/>'
            . '</cols>';
        $xml .= '<sheetData>';
        $xml .= '<row r="1" ht="96.75" customHeight="1">' . $this->celdaTexto('A1', 5, $this->instructivoTitulo) . '</row>';
        $xml .= '<row r="2" ht="409.6" customHeight="1">' . $this->celdaTexto('A2', 6, $this->instructivoTexto) . '</row>';
        $xml .= '</sheetData>';
        $xml .= '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>';
        $xml .= '</worksheet>';
        return $xml;
    }

    private function alturaFila($f)
    {
        $anchos = [
            'actividad' => 38.43, 'objetivo' => 8.43, 'descripcion' => 60.29,
            'estados' => 29.14, 'municipios' => 8.43, 'lugar' => 31.57,
            'hora' => 15.29, 'observaciones' => 31.43,
        ];
        $lineas = 1;
        foreach ($anchos as $campo => $ancho) {
            $n = 0;
            foreach (explode("\n", (string) $f[$campo]) as $parte) {
                $n += max(1, (int) ceil(mb_strlen($parte) / max(8, $ancho)));
            }
            $lineas = max($lineas, $n);
        }
        return min(110, max(18, $lineas * 14.5 + 4));
    }

    private function celdaTexto($ref, $estilo, $texto)
    {
        return '<c r="' . $ref . '" s="' . $estilo . '" t="inlineStr"><is><t xml:space="preserve">'
            . $this->esc($texto) . '</t></is></c>';
    }

    private function celdaVacia($ref, $estilo)
    {
        return '<c r="' . $ref . '" s="' . $estilo . '"/>';
    }

    private function celdaFecha($ref, $estilo, $ymd)
    {
        $ts = gmmktime(12, 0, 0, (int) substr($ymd, 5, 2), (int) substr($ymd, 8, 2), (int) substr($ymd, 0, 4));
        $serial = $ts / 86400 + 25569;
        return '<c r="' . $ref . '" s="' . $estilo . '"><v>' . $serial . '</v></c>';
    }

    private function esc($texto)
    {
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $texto);
        return htmlspecialchars($texto, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function contentTypes()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .= '<Default Extension="xml" ContentType="application/xml"/>';
        $xml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        for ($i = 1; $i <= 9; $i++) {
            $xml .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $xml .= '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>';
        $xml .= '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>';
        $xml .= '</Types>';
        return $xml;
    }

    private function relsRaiz()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function workbook()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<workbookPr/>';
        $xml .= '<bookViews><workbookView activeTab="1"/></bookViews>';
        $xml .= '<sheets>';
        $i = 1;
        foreach ($this->hojas as $nombre => $color) {
            $xml .= '<sheet name="' . $this->esc($nombre) . '" sheetId="' . $i . '" r:id="rId' . $i . '"/>';
            $i++;
        }
        $xml .= '</sheets>';
        $xml .= '</workbook>';
        return $xml;
    }

    private function workbookRels()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        for ($i = 1; $i <= 9; $i++) {
            $xml .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }
        $xml .= '<Relationship Id="rId10" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $xml .= '</Relationships>';
        return $xml;
    }

    private function coreProps()
    {
        $fecha = gmdate('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>AGENDA FUNDACITE YARACUY</dc:title>'
            . '<dc:creator>SIGPAF</dc:creator>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $fecha . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $fecha . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function appProps()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>SIGPAF</Application>'
            . '</Properties>';
    }

    private function styles()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="165" formatCode="dd/mm/yy"/></numFmts>'
            . '<fonts count="5">'
            . '<font><sz val="11"/><name val="Arial"/></font>'
            . '<font><b/><sz val="12"/><name val="Arial"/></font>'
            . '<font><b/><sz val="11"/><name val="Arial"/></font>'
            . '<font><sz val="24"/><name val="Arial"/></font>'
            . '<font><sz val="16"/><name val="Arial"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE0C2CD"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color auto="1"/></left><right style="thin"><color auto="1"/></right><top style="thin"><color auto="1"/></top><bottom style="thin"><color auto="1"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="7">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
