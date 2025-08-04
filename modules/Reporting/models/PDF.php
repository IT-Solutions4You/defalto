<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

use Mpdf\Mpdf;

class Reporting_PDF_Model extends Vtiger_Base_Model
{
    public $mpdf = false;

    public static function getInstance(): self
    {
        $instance = new self();
        $instance->mpdf = new mPDF();

        return $instance;
    }

    public function getPDF(): string
    {
        $file = sprintf('%s/PDF%s.pdf', rtrim(decideFilePath(), '/'), time());
        $this->mpdf->Output($file);

        return $file;
    }

    public function setContent($value): void
    {
        $this->mpdf->WriteHTML($value);
    }
}