<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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