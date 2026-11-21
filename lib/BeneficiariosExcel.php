<?php
// Listado de beneficiarios en Excel

class BeneficiariosExcel
{
    private $columnas = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    public function generar($registros)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'beneficiarios');
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
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->hoja($registros));
        $zip->close();

        $contenido = file_get_contents($tmp);
        unlink($tmp);
        return $contenido;
    }

    private function hoja($registros)
    {
        $ultimaFila = max(3, count($registros) + 3);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetPr><tabColor rgb="FF138A7A"/></sheetPr>';
        $xml .= '<sheetViews><sheetView showGridLines="0" workbookViewId="0">'
            . '<pane ySplit="3" topLeftCell="A4" activePane="bottomLeft" state="frozen"/>'
            . '</sheetView></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="18"/>';
        $xml .= '<cols>'
            . '<col min="1" max="1" width="17" customWidth="1"/>'
            . '<col min="2" max="2" width="18" customWidth="1"/>'
            . '<col min="3" max="4" width="27" customWidth="1"/>'
            . '<col min="5" max="5" width="18" customWidth="1"/>'
            . '<col min="6" max="6" width="9" customWidth="1"/>'
            . '<col min="7" max="7" width="17" customWidth="1"/>'
            . '<col min="8" max="8" width="14" customWidth="1"/>'
            . '<col min="9" max="10" width="15" customWidth="1"/>'
            . '</cols>';
        $xml .= '<sheetData>';
        $xml .= '<row r="1" ht="30" customHeight="1">'
            . $this->celdaTexto('A1', 1, 'BENEFICIARIOS REGISTRADOS - SIGPAF') . '</row>';
        $xml .= '<row r="2" ht="23" customHeight="1">'
            . $this->celdaTexto('A2', 2, 'FUNDACITE Yaracuy | Fecha de emisión: ' . date('d/m/Y h:i A')
                . ' | Total: ' . count($registros) . ' beneficiario(s)') . '</row>';

        $encabezados = [
            'Código', 'Cédula', 'Nombres', 'Apellidos', 'Fecha de nacimiento',
            'Edad', 'Nacionalidad', 'Sexo', 'Asistencias', 'Actividades'
        ];
        $xml .= '<row r="3" ht="31" customHeight="1">';
        foreach ($encabezados as $i => $encabezado) {
            $xml .= $this->celdaTexto($this->columnas[$i] . '3', 3, $encabezado);
        }
        $xml .= '</row>';

        $fila = 4;
        foreach ($registros as $r) {
            $xml .= '<row r="' . $fila . '" ht="25" customHeight="1">';
            $xml .= $this->celdaTexto('A' . $fila, 4, $r['idBeneficiario'] ?? '');
            $xml .= $this->celdaTexto('B' . $fila, 4, $r['cedulaBeneficiario'] ?? '');
            $xml .= $this->celdaTexto('C' . $fila, 4, $r['nombresBeneficiario'] ?? '');
            $xml .= $this->celdaTexto('D' . $fila, 4, $r['apellidosBeneficiario'] ?? '');
            $fecha = !empty($r['fechaNacBeneficiario'])
                ? date('d/m/Y', strtotime($r['fechaNacBeneficiario'])) : '';
            $xml .= $this->celdaTexto('E' . $fila, 5, $fecha);
            $xml .= $this->celdaNumero('F' . $fila, 5, $r['edad'] ?? null);
            $xml .= $this->celdaTexto('G' . $fila, 4, $r['nacionalidad'] ?? '');
            $xml .= $this->celdaTexto('H' . $fila, 5, $r['sexoBeneficiario'] ?? '');
            $xml .= $this->celdaNumero('I' . $fila, 5, $r['totalAsistencias'] ?? 0);
            $xml .= $this->celdaNumero('J' . $fila, 5, $r['totalActividades'] ?? 0);
            $xml .= '</row>';
            $fila++;
        }
        $xml .= '</sheetData>';
        $xml .= '<autoFilter ref="A3:J' . $ultimaFila . '"/>';
        $xml .= '<mergeCells count="2"><mergeCell ref="A1:J1"/><mergeCell ref="A2:J2"/></mergeCells>';
        $xml .= '<pageMargins left="0.35" right="0.35" top="0.5" bottom="0.5" header="0.3" footer="0.3"/>';
        $xml .= '<pageSetup orientation="landscape" paperSize="1" fitToWidth="1" fitToHeight="0"/>';
        $xml .= '</worksheet>';
        return $xml;
    }

    private function celdaTexto($ref, $estilo, $texto)
    {
        return '<c r="' . $ref . '" s="' . $estilo . '" t="inlineStr"><is><t xml:space="preserve">'
            . $this->esc($texto) . '</t></is></c>';
    }

    private function celdaNumero($ref, $estilo, $numero)
    {
        if ($numero === null || $numero === '') {
            return '<c r="' . $ref . '" s="' . $estilo . '"/>';
        }
        return '<c r="' . $ref . '" s="' . $estilo . '"><v>' . (int) $numero . '</v></c>';
    }

    private function esc($texto)
    {
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $texto);
        return htmlspecialchars($texto, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function contentTypes()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
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
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<workbookPr/><bookViews><workbookView activeTab="0"/></bookViews>'
            . '<sheets><sheet name="Beneficiarios" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRels()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function coreProps()
    {
        $fecha = gmdate('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Beneficiarios registrados - SIGPAF</dc:title><dc:creator>SIGPAF</dc:creator>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $fecha . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $fecha . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function appProps()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>SIGPAF</Application></Properties>';
    }

    private function styles()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="5">'
            . '<font><sz val="10"/><name val="Arial"/></font>'
            . '<font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>'
            . '<font><sz val="10"/><color rgb="FF475569"/><name val="Arial"/></font>'
            . '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>'
            . '<font><sz val="10"/><color rgb="FF1E293B"/><name val="Arial"/></font>'
            . '</fonts>'
            . '<fills count="5">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF16305E"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE8F3F1"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF138A7A"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFD9E2EC"/></left><right style="thin"><color rgb="FFD9E2EC"/></right><top style="thin"><color rgb="FFD9E2EC"/></top><bottom style="thin"><color rgb="FFD9E2EC"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="6">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
