<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Penulis .xlsx minimalis (tanpa library tambahan, butuh ekstensi PHP "zip").
 * Cukup untuk satu sheet: teks, angka, header berwarna, baris zebra, freeze header, filter.
 */
class XlsxSederhana
{
    public const S_HEADER    = 1;
    public const S_ZEBRA     = 2;
    public const S_NUM       = 3;
    public const S_NUM_ZEBRA = 4;
    public const S_BOLD      = 5;
    public const S_BOLD_NUM  = 6;

    private array $rows = [];
    private array $lebar = [];
    private int $barisFilter = 0;

    public function __construct(private string $namaSheet = 'Sheet1')
    {
    }

    public function lebarKolom(array $lebar): self
    {
        $this->lebar = array_values($lebar);
        return $this;
    }

    /** Aktifkan freeze baris pertama + autofilter sampai kolom terakhir header. */
    public function aktifkanFilterHeader(): self
    {
        $this->barisFilter = 1;
        return $this;
    }

    /** Tiap sel: nilai skalar, atau [nilai, styleId]. */
    public function tambahBaris(array $sel): self
    {
        $this->rows[] = $sel;
        return $this;
    }

    public function simpan(string $path): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat file Excel.');
        }

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->esc(substr($this->namaSheet, 0, 31)) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet());

        $zip->close();
    }

    private function sheet(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        if ($this->barisFilter) {
            $xml .= '<sheetViews><sheetView workbookViewId="0">'
                . '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
                . '</sheetView></sheetViews>';
        }

        if ($this->lebar) {
            $xml .= '<cols>';
            foreach ($this->lebar as $i => $w) {
                $n = $i + 1;
                $xml .= '<col min="' . $n . '" max="' . $n . '" width="' . $w . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        $maxKolom = 0;
        foreach ($this->rows as $r => $sel) {
            $nomorBaris = $r + 1;
            $xml .= '<row r="' . $nomorBaris . '">';
            $kolom = 0;
            foreach ($sel as $sel1) {
                $kolom++;
                [$nilai, $style] = is_array($sel1) ? [$sel1[0], $sel1[1] ?? 0] : [$sel1, 0];
                $ref = $this->hurufKolom($kolom) . $nomorBaris;
                $s = $style ? ' s="' . $style . '"' : '';

                if ($nilai === null || $nilai === '') {
                    $xml .= $style ? '<c r="' . $ref . '"' . $s . '/>' : '';
                } elseif (is_int($nilai) || is_float($nilai)) {
                    $xml .= '<c r="' . $ref . '"' . $s . '><v>' . $nilai . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '"' . $s . ' t="inlineStr"><is><t xml:space="preserve">'
                        . $this->esc((string) $nilai) . '</t></is></c>';
                }
            }
            $maxKolom = max($maxKolom, $kolom);
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';

        if ($this->barisFilter && $this->rows) {
            $xml .= '<autoFilter ref="A1:' . $this->hurufKolom(count($this->rows[0])) . count($this->rows) . '"/>';
        }

        return $xml . '</worksheet>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            .   '<font><sz val="11"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="4">'
            .   '<fill><patternFill patternType="none"/></fill>'
            .   '<fill><patternFill patternType="gray125"/></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FFF3E7C9"/><bgColor indexed="64"/></patternFill></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FFF7F3EA"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            .   '<border><left/><right/><top/><bottom/><diagonal/></border>'
            .   '<border><left/><right/><top/><bottom style="thin"><color rgb="FFB9B1A3"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="7">'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .   '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .   '<xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"/>'
            .   '<xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .   '<xf numFmtId="3" fontId="0" fillId="3" borderId="0" xfId="0" applyNumberFormat="1" applyFill="1"/>'
            .   '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .   '<xf numFmtId="3" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyNumberFormat="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function hurufKolom(int $n): string
    {
        $s = '';
        while ($n > 0) {
            $m = ($n - 1) % 26;
            $s = chr(65 + $m) . $s;
            $n = intdiv($n - 1, 26);
        }
        return $s;
    }

    private function esc(string $v): string
    {
        // buang karakter kontrol yang tidak valid di XML 1.0
        $v = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $v) ?? '';
        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
