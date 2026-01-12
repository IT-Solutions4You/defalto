<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_SaveAjax_Action extends Core_Controller_Action
{
    function __construct()
    {
        parent::__construct();

        $Methods = ['SaveProductBlock', 'deleteProductBlocks', 'SavePDFBreakline', 'SavePDFImages'];

        foreach ($Methods as $method) {
            $this->exposeMethod($method);
        }
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function SavePDFBreakline(Vtiger_Request $request)
    {
        $crmid = $request->get('return_id');
        $result = [];
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_pdfmaker_breakline WHERE crmid = ?', [$crmid]);

        $sql2 = 'INSERT INTO vtiger_pdfmaker_breakline (crmid, productid, sequence, show_header, show_subtotal) VALUES (?,?,?,?,?)';
        $show_header_val = $show_subtotal_val = "0";

        if ($request->has('show_header') && !$request->isEmpty('show_header')) {
            $show_header_val = $request->get('show_header');
        }

        if ($request->has('show_subtotal') && !$request->isEmpty('show_subtotal')) {
            $show_subtotal_val = $request->get('show_subtotal');
        }

        $RequestAllData = $request->getAll();

        foreach ($RequestAllData as $iad_name => $iad_value) {
            if (substr($iad_name, 0, 14) == 'ItemPageBreak_' && $iad_value == '1') {
                [$i, $productid, $sequence] = explode('_', $iad_name, 3);
                $adb->pquery($sql2, [$crmid, $productid, $sequence, $show_header_val, $show_subtotal_val]);
            }
        }

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }

    public function SavePDFImages(Vtiger_Request $request)
    {
        $result = [];
        $crmid = $request->get('return_id');
        $adb = PearDatabase::getInstance();

        $sql1 = 'DELETE FROM vtiger_pdfmaker_images WHERE crmid=?';
        $adb->pquery($sql1, [$crmid]);

        $sql2 = 'INSERT INTO vtiger_pdfmaker_images (crmid, productid, sequence, attachmentid, width, height) VALUES (?, ?, ?, ?, ?, ?)';

        $R_Data = $request->getAll();

        foreach ($R_Data as $key => $value) {
            if (strpos($key, 'img_') !== false) {
                [$bin, $productid, $sequence] = explode("_", $key);

                if ($value != 'no_image') {
                    $width = $R_Data['width_' . $productid . '_' . $sequence];
                    $height = $R_Data['height_' . $productid . '_' . $sequence];

                    if (!is_numeric($width) || $width > 999) {
                        $width = 0;
                    }

                    if (!is_numeric($height) || $height > 999) {
                        $height = 0;
                    }
                } else {
                    $height = $width = $value = 0;
                }

                $adb->pquery($sql2, [$crmid, $productid, $sequence, $value, $width, $height]);
            }
        }

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }
}