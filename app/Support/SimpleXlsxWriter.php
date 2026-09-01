<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

class SimpleXlsxWriter
{
    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function write(array $headings, iterable $rows): string
    {
        $xlsxPath = tempnam(sys_get_temp_dir(), 'members_xlsx_');
        $sheetPath = tempnam(sys_get_temp_dir(), 'members_sheet_');
        $sheet = fopen($sheetPath, 'wb');

        if ($xlsxPath === false || $sheetPath === false || $sheet === false) {
            throw new RuntimeException('Unable to create the Excel export.');
        }

        fwrite($sheet, $this->worksheetStart($headings));
        $this->writeRow($sheet, 1, $headings, 1);

        $rowNumber = 2;
        foreach ($rows as $row) {
            $this->writeRow($sheet, $rowNumber++, $row);
        }

        $lastColumn = $this->columnLetter(count($headings));
        fwrite($sheet, '</sheetData><autoFilter ref="A1:'.$lastColumn.'1"/></worksheet>');
        fclose($sheet);

        $zip = new ZipArchive;
        if ($zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($sheetPath);
            @unlink($xlsxPath);
            throw new RuntimeException('Unable to package the Excel export.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFile($sheetPath, 'xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($sheetPath);

        return $xlsxPath;
    }

    /** @param resource $sheet */
    private function writeRow($sheet, int $rowNumber, array $values, int $style = 0): void
    {
        fwrite($sheet, '<row r="'.$rowNumber.'">');

        foreach (array_values($values) as $index => $value) {
            $reference = $this->columnLetter($index + 1).$rowNumber;
            $text = $this->xml((string) ($value ?? ''));
            fwrite($sheet, '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.$text.'</t></is></c>');
        }

        fwrite($sheet, '</row>');
    }

    private function worksheetStart(array $headings): string
    {
        $columns = '';

        foreach ($headings as $index => $heading) {
            $width = min(35, max(12, strlen((string) $heading) + 4));
            $column = $index + 1;
            $columns .= '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols>'.$columns.'</cols><sheetData>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
 <fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>
 <fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill></fills>
 <borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FF808080"/></left><right style="thin"><color rgb="FF808080"/></right><top style="thin"><color rgb="FF808080"/></top><bottom style="thin"><color rgb="FF808080"/></bottom><diagonal/></border></borders>
 <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
 <cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0"><alignment vertical="center" wrapText="1"/></xf></cellXfs>
 <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>';
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Members" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private function columnLetter(int $number): string
    {
        $letter = '';
        while ($number > 0) {
            $number--;
            $letter = chr(65 + ($number % 26)).$letter;
            $number = intdiv($number, 26);
        }

        return $letter;
    }

    private function xml(string $value): string
    {
        $value = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? '';

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
