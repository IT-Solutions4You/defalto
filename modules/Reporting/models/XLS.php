<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reporting_XLS_Model extends Vtiger_Base_Model
{
    public $spreadsheet = false;
    public $activeWorksheet = false;

    public static function getInstance(): self
    {
        $instance = new self();
        $instance->retrieveSpreadsheet();

        return $instance;
    }

    /**
     * @param int $row
     * @param int $column
     *
     * @return string
     */
    public function generateCellId(int $row, int $column): string
    {
        $cellID = '';

        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $cellID = chr(65 + $remainder) . $cellID;
            $column = intdiv($column - 1, 26);
        }

        $cellID .= $row;

        return $cellID;
    }

    public function retrieveSpreadsheet(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->activeWorksheet = $this->spreadsheet->getActiveSheet();
    }

    public function setCellValues(array $data): void
    {
        $tableRowNum = 0;

        foreach ($data as $tableRows) {
            $tableRowNum++;
            $tableColumnNum = 0;

            foreach ($tableRows as $tableColumn) {
                $tableColumnNum++;
                $cellId = $this->generateCellId($tableRowNum, $tableColumnNum);
                $cellValue = $this->generateCellValue($tableColumn);

                $this->activeWorksheet->setCellValue($cellId, $cellValue);
            }
        }
    }

    public function generateCellValue($value): string
    {
        return strip_tags($value);
    }

    public function getXLXS(): string
    {
        $url = sprintf('%s/XLX%s.xlsx', rtrim(decideFilePath(), '/'), time());

        $writer = new Xlsx($this->spreadsheet);
        $writer->save($url);

        return $url;
    }
}