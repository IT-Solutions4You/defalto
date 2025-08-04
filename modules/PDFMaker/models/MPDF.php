<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_MPDF_Model extends \Mpdf\Mpdf
{
    public $PDFMakerRecord;
    public $PDFMakerTemplateid;
    public $PDFMakerSubtotalAble;
    public $PDFMakerSubtotalsArray;
    public $PDFMakerDispHeader;
    public $PDFMakerDispFooter;
    public $PDFMakerSplitCounter = 0;
    public $progressBar;
    public $progbar_heading;
    public $progbar_altHTML;
    public $useGraphs;
    public $showStats;

    public function __construct(array $config = [], $container = null)
    {
        parent::__construct($config, $container);

        require 'MPDFConfig.php';
    }

    public $javascript;
    public $n_js;

    function _putresources()
    {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
    }

    function _putjavascript()
    {
        $this->_newobj();
        $this->n_js = $this->n;
        $this->_out('<<');
        $this->_out('/Names [(EmbeddedJS) ' . ($this->n + 1) . ' 0 R]');
        $this->_out('>>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<<');
        $this->_out('/S /JavaScript');
        $this->_out('/JS ' . $this->_textstring($this->javascript));
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putcatalog()
    {
        parent::_putcatalog();
        if (!empty($this->javascript)) {
            $this->_out('/Names <</JavaScript ' . ($this->n_js) . ' 0 R>>');
        }
    }

    function AutoPrint($dialog = false)
    {
        //Open the print dialog or start printing immediately on the standard printer
        $param = ($dialog ? 'true' : 'false');
        $script = "print($param);";
        $this->IncludeJS($script);
    }

    function IncludeJS($script)
    {
        $this->javascript = $script;
    }

    function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        //Print on a shared printer (requires at least Acrobat 6)
        $script = "var pp = getPrintParams();";
        if ($dialog) {
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
        } else {
            $script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
        }
        $script .= "pp.printerName = '\\\\\\\\" . $server . "\\\\" . $printer . "';";
        $script .= "print(pp);";
        $this->IncludeJS($script);
    }
}