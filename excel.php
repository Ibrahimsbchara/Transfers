<?php
declare(strict_types=1);

/**
 * Minimal self-contained XLSX generator.
 * Produces files compatible with Excel 2007+, LibreOffice, Google Sheets.
 * Uses PHP's built-in ZipArchive — no external dependencies.
 */
class XLSXWriter
{
    // ── Style index constants ──────────────────────────────────────────
    public const S_DEFAULT      = 0;  // unstyled
    public const S_HEADER       = 1;  // bold 14pt red, center
    public const S_DATA_CENTER  = 2;  // bold 14pt black, center, wrap
    public const S_DATA_LEFT    = 3;  // bold 14pt black, left-align, wrap
    public const S_DATA_AMOUNT  = 4;  // bold 14pt black, center, #,##0.00
    public const S_TOTAL_LABEL  = 5;  // bold 11pt red, center
    public const S_TOTAL_AMOUNT = 6;  // bold 11pt red, center, #,##0.00
    public const S_TITLE        = 7;  // bold 16pt white, center, dark-blue fill

    private array  $strings    = [];   // shared string pool (value → index)
    private array  $stringList = [];   // ordered list for XML
    private array  $rows       = [];   // [rowNum][colNum] → cell data
    private array  $colWidths  = [];   // [colNum] → width
    private array  $merges     = [];   // [r1, c1, r2, c2]
    private int    $maxRow     = 0;
    private int    $maxCol     = 0;
    private string $sheetName;

    public function __construct(string $sheetName)
    {
        // Excel sheet names: max 31 chars, no special chars
        $this->sheetName = substr(preg_replace('/[\/\\\?\*\[\]:]+/', '-', $sheetName), 0, 31);
    }

    // ── Public write API ───────────────────────────────────────────────

    public function writeString(int $row, int $col, string $value, int $style = self::S_DEFAULT): void
    {
        $idx = $this->internString($value);
        $this->rows[$row][$col] = ['t' => 's', 'v' => $idx, 's' => $style];
        $this->updateDims($row, $col);
    }

    public function writeNumber(int $row, int $col, float $value, int $style = self::S_DEFAULT): void
    {
        $this->rows[$row][$col] = ['t' => 'n', 'v' => $value, 's' => $style];
        $this->updateDims($row, $col);
    }

    public function writeFormula(int $row, int $col, string $formula, int $style = self::S_DEFAULT): void
    {
        $this->rows[$row][$col] = ['t' => 'f', 'v' => $formula, 's' => $style];
        $this->updateDims($row, $col);
    }

    public function setColWidth(int $col, float $width): void
    {
        $this->colWidths[$col] = $width;
    }

    public function addMerge(int $r1, int $c1, int $r2, int $c2): void
    {
        $this->merges[] = [$r1, $c1, $r2, $c2];
    }

    /** Generate and return the raw XLSX binary string. */
    public function generate(): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml',        $this->xmlContentTypes());
        $zip->addFromString('_rels/.rels',                $this->xmlRels());
        $zip->addFromString('xl/workbook.xml',            $this->xmlWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xmlWorkbookRels());
        $zip->addFromString('xl/styles.xml',              $this->xmlStyles());
        $zip->addFromString('xl/sharedStrings.xml',       $this->xmlSharedStrings());
        $zip->addFromString('xl/worksheets/sheet1.xml',   $this->xmlWorksheet());

        $zip->close();
        $data = (string)file_get_contents($tmp);
        unlink($tmp);
        return $data;
    }

    // ── Private helpers ────────────────────────────────────────────────

    private function internString(string $s): int
    {
        if (!array_key_exists($s, $this->strings)) {
            $this->strings[$s] = count($this->stringList);
            $this->stringList[] = $s;
        }
        return $this->strings[$s];
    }

    private function updateDims(int $row, int $col): void
    {
        if ($row > $this->maxRow) $this->maxRow = $row;
        if ($col > $this->maxCol) $this->maxCol = $col;
    }

    private function colLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(65 + ($col % 26)) . $letter;
            $col    = intdiv($col, 26);
        }
        return $letter;
    }

    private function ref(int $row, int $col): string
    {
        return $this->colLetter($col) . $row;
    }

    private function xe(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1, 'UTF-8');
    }

    // ── XML generators ─────────────────────────────────────────────────

    private function xmlContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/xl/sharedStrings.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>';
    }

    private function xmlRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
    Target="xl/workbook.xml"/>
</Relationships>';
    }

    private function xmlWorkbook(): string
    {
        $n = $this->xe($this->sheetName);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . $n . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
    }

    private function xmlWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
    Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"
    Target="styles.xml"/>
  <Relationship Id="rId3"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"
    Target="sharedStrings.xml"/>
</Relationships>';
    }

    /**
     * Styles sheet.
     *
     * cellXfs order (matches S_* constants above):
     *   0 = default
     *   1 = header      : font1 (bold 14 red),   fill0 (none),      center
     *   2 = data_center : font2 (bold 14 black),  fill0,             center + wrap
     *   3 = data_left   : font2,                  fill0,             left   + wrap
     *   4 = data_amount : font2,                  fill0,             center, numFmt #,##0.00
     *   5 = total_label : font3 (bold 11 red),    fill0,             center
     *   6 = total_amount: font3,                  fill0,             center, numFmt #,##0.00
     *   7 = title       : font4 (bold 16 white),  fill2 (dark blue), center
     */
    private function xmlStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">

  <numFmts count="1">
    <numFmt numFmtId="164" formatCode="#,##0.00"/>
  </numFmts>

  <fonts count="5">
    <!-- 0: default -->
    <font><sz val="11"/><name val="Calibri"/></font>
    <!-- 1: bold 14pt red -->
    <font><b/><sz val="14"/><color rgb="FFFF0000"/><name val="Calibri"/></font>
    <!-- 2: bold 14pt black -->
    <font><b/><sz val="14"/><color rgb="FF000000"/><name val="Calibri"/></font>
    <!-- 3: bold 11pt red -->
    <font><b/><sz val="11"/><color rgb="FFFF0000"/><name val="Calibri"/></font>
    <!-- 4: bold 16pt white -->
    <font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
  </fonts>

  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <!-- 2: solid dark navy #1B3A6B -->
    <fill><patternFill patternType="solid"><fgColor rgb="FF1B3A6B"/><bgColor indexed="64"/></patternFill></fill>
  </fills>

  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>

  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>

  <cellXfs count="8">
    <!-- 0: default -->
    <xf numFmtId="0"   fontId="0" fillId="0" borderId="0" xfId="0"/>
    <!-- 1: header (red bold, center) -->
    <xf numFmtId="0"   fontId="1" fillId="0" borderId="0" xfId="0"
        applyFont="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <!-- 2: data center (black bold, center, wrap) -->
    <xf numFmtId="0"   fontId="2" fillId="0" borderId="0" xfId="0"
        applyFont="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <!-- 3: data left (black bold, left, wrap) -->
    <xf numFmtId="0"   fontId="2" fillId="0" borderId="0" xfId="0"
        applyFont="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center" wrapText="1"/>
    </xf>
    <!-- 4: data amount (black bold, center, #,##0.00) -->
    <xf numFmtId="164" fontId="2" fillId="0" borderId="0" xfId="0"
        applyFont="1" applyNumberFormat="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <!-- 5: total label (red bold 11pt, center) -->
    <xf numFmtId="0"   fontId="3" fillId="0" borderId="0" xfId="0"
        applyFont="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <!-- 6: total amount (red bold 11pt, center, #,##0.00) -->
    <xf numFmtId="164" fontId="3" fillId="0" borderId="0" xfId="0"
        applyFont="1" applyNumberFormat="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <!-- 7: title (white bold 16pt, dark-blue fill, center) -->
    <xf numFmtId="0"   fontId="4" fillId="2" borderId="0" xfId="0"
        applyFont="1" applyFill="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
  </cellXfs>

</styleSheet>';
    }

    private function xmlSharedStrings(): string
    {
        $count = count($this->stringList);
        $xml   = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml  .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
               . ' count="' . $count . '" uniqueCount="' . $count . '">' . "\n";
        foreach ($this->stringList as $s) {
            $xml .= '  <si><t xml:space="preserve">' . $this->xe($s) . '</t></si>' . "\n";
        }
        $xml .= '</sst>';
        return $xml;
    }

    private function xmlWorksheet(): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";

        // Column widths
        if (!empty($this->colWidths)) {
            ksort($this->colWidths);
            $xml .= '  <cols>' . "\n";
            foreach ($this->colWidths as $col => $w) {
                $xml .= '    <col min="' . $col . '" max="' . $col . '" width="' . $w . '" customWidth="1"/>' . "\n";
            }
            $xml .= '  </cols>' . "\n";
        }

        // Sheet data
        $xml .= '  <sheetData>' . "\n";
        for ($r = 1; $r <= $this->maxRow; $r++) {
            $xml .= '    <row r="' . $r . '">' . "\n";
            if (isset($this->rows[$r])) {
                ksort($this->rows[$r]);
                foreach ($this->rows[$r] as $col => $cell) {
                    $ref = $this->ref($r, $col);
                    $s   = $cell['s'];
                    if ($cell['t'] === 's') {
                        $xml .= '      <c r="' . $ref . '" t="s" s="' . $s . '"><v>' . $cell['v'] . '</v></c>' . "\n";
                    } elseif ($cell['t'] === 'n') {
                        $xml .= '      <c r="' . $ref . '" s="' . $s . '"><v>' . $cell['v'] . '</v></c>' . "\n";
                    } elseif ($cell['t'] === 'f') {
                        $xml .= '      <c r="' . $ref . '" s="' . $s . '"><f>' . $this->xe($cell['v']) . '</f></c>' . "\n";
                    }
                }
            }
            $xml .= '    </row>' . "\n";
        }
        $xml .= '  </sheetData>' . "\n";

        // Freeze top rows
        $xml .= '  <sheetViews><sheetView workbookViewId="0"><pane ySplit="2" topLeftCell="A3" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>' . "\n";

        // Merge cells
        if (!empty($this->merges)) {
            $xml .= '  <mergeCells count="' . count($this->merges) . '">' . "\n";
            foreach ($this->merges as [$r1, $c1, $r2, $c2]) {
                $xml .= '    <mergeCell ref="' . $this->ref($r1, $c1) . ':' . $this->ref($r2, $c2) . '"/>' . "\n";
            }
            $xml .= '  </mergeCells>' . "\n";
        }

        $xml .= '</worksheet>';
        return $xml;
    }
}

// ── Public function called by index.php ────────────────────────────────────

/**
 * @param  string $dateName  Sheet tab name (e.g. "APRIL 2026")
 * @param  array  $transfers Array of transfer rows from DB
 * @return string            Raw XLSX binary
 */
function generate_excel(string $dateName, array $transfers): string
{
    $w = new XLSXWriter($dateName);

    // Column widths (characters) — matching the original file layout
    $w->setColWidth(1, 22);   // A  VESSEL
    $w->setColWidth(2, 38);   // B  FROM
    $w->setColWidth(3, 40);   // C  TO
    $w->setColWidth(4, 15);   // D  AMOUNT
    $w->setColWidth(5,  5);   // E  spacer
    $w->setColWidth(6, 65);   // F  BANK DETAILS
    $w->setColWidth(7, 22);   // G  PLACE OF DELIVERY

    // ── Row 1: title bar (merged A1:G1) ───────────────────────────────
    $w->writeString(1, 1, strtoupper($dateName) . '  —  TRANSFER RECORDS', XLSXWriter::S_TITLE);
    for ($c = 2; $c <= 7; $c++) {
        $w->writeString(1, $c, '', XLSXWriter::S_TITLE);
    }
    $w->addMerge(1, 1, 1, 7);

    // ── Row 2: column headers ─────────────────────────────────────────
    $headers = [
        1 => 'VESSEL', 2 => 'FROM', 3 => 'TO',
        4 => 'AMOUNT', 6 => 'BANK NAME', 7 => 'PLACE OF DELIVERY',
    ];
    foreach ($headers as $col => $txt) {
        $w->writeString(2, $col, $txt, XLSXWriter::S_HEADER);
    }

    // ── Data rows ─────────────────────────────────────────────────────
    $currentRow = 3;

    foreach ($transfers as $t) {
        // Build bank-detail lines for column F
        $lines = [];
        if (!empty($t['bank_name']))      $lines[] = 'BANK NAME: '       . $t['bank_name'];
        if (!empty($t['account_number'])) $lines[] = 'ACCOUNT NUMBER: '  . $t['account_number'];
        if (!empty($t['iban']))           $lines[] = 'IBAN: '            . $t['iban'];
        if (!empty($t['swift_code']))     $lines[] = 'SWIFT CODE: '      . $t['swift_code'];
        if (!empty($t['branch']))         $lines[] = 'BRANCH: '          . $t['branch'];
        if (!empty($t['mobile_number']))  $lines[] = 'MOBILE: '          . $t['mobile_number'];
        if (!empty($t['cid']))            $lines[] = 'CID: '             . $t['cid'];

        if (empty($lines)) {
            $lines = [''];
        }

        // Main data row
        $r0 = $currentRow;
        $w->writeString($r0, 1, (string)($t['vessel']        ?? ''), XLSXWriter::S_DATA_CENTER);
        $w->writeString($r0, 2, (string)($t['sender_name']   ?? ''), XLSXWriter::S_DATA_CENTER);
        $w->writeString($r0, 3, (string)($t['receiver_name'] ?? ''), XLSXWriter::S_DATA_CENTER);
        $w->writeNumber($r0, 4, (float)($t['amount']         ?? 0),  XLSXWriter::S_DATA_AMOUNT);
        $w->writeString($r0, 6, $lines[0],                            XLSXWriter::S_DATA_LEFT);
        $w->writeString($r0, 7, (string)($t['place_of_delivery'] ?? ''), XLSXWriter::S_DATA_CENTER);

        // Extra bank-detail rows (col F only)
        for ($i = 1, $iMax = count($lines); $i < $iMax; $i++) {
            $w->writeString($currentRow + $i, 6, $lines[$i], XLSXWriter::S_DATA_LEFT);
        }

        $currentRow += count($lines) + 1; // +1 empty separator row
    }

    // ── Total row ─────────────────────────────────────────────────────
    $w->writeString($currentRow, 3, 'TOTAL',                         XLSXWriter::S_TOTAL_LABEL);
    $w->writeFormula($currentRow, 4, 'SUM(D3:D' . ($currentRow - 1) . ')', XLSXWriter::S_TOTAL_AMOUNT);

    return $w->generate();
}
