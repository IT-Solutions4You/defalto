<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PDFMaker_SaveAjax_Action extends Vtiger_Action_Controller
{
    function __construct()
    {
        parent::__construct();

        $Methods = array('SaveProductBlock', 'deleteProductBlocks', 'SavePDFBreakline', 'SavePDFImages');

        foreach ($Methods as $method) {
            $this->exposeMethod($method);
        }
    }

    public function checkPermission(Vtiger_Request $request)
    {
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function SaveProductBlock(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        $adb = PearDatabase::getInstance();
        $tplid = $request->get('tplid');
        $template_name = $request->get('template_name');
        $body = $request->get('body');

        if (isset($tplid) && $tplid != '') {
            $sql = 'UPDATE vtiger_pdfmaker_productbloc_tpl SET name=?, body=? WHERE id=?';
            $adb->pquery($sql, array($template_name, $body, $tplid));
        } else {
            $sql = 'INSERT INTO vtiger_pdfmaker_productbloc_tpl(name, body) VALUES(?,?)';
            $adb->pquery($sql, array($template_name, $body));
        }

        header("Location:index.php?module=PDFMaker&view=ProductBlocks");
    }

    public function deleteProductBlocks(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $sql = 'DELETE FROM vtiger_pdfmaker_productbloc_tpl WHERE id IN (';
        $params = array();

        foreach ($_REQUEST as $key => $val) {
            if (substr($key, 0, 4) == 'chx_' && $val == 'on') {
                list($dump, $id) = explode('_', $key, 2);

                if (is_numeric($id)) {
                    $sql .= '?,';
                    array_push($params, $id);
                }
            }
        }

        if (count($params) > 0) {
            $sql = rtrim($sql, ',') . ')';
            $adb->pquery($sql, $params);
        }

        header('Location:index.php?module=PDFMaker&view=ProductBlocks');
    }

    public function SavePDFBreakline(Vtiger_Request $request)
    {
        $crmid = $request->get('return_id');
        $result = array();
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_pdfmaker_breakline WHERE crmid = ?', array($crmid));

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
                list($i, $productid, $sequence) = explode('_', $iad_name, 3);
                $adb->pquery($sql2, array($crmid, $productid, $sequence, $show_header_val, $show_subtotal_val));
            }
        }


        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }

    public function SavePDFImages(Vtiger_Request $request)
    {
        $result = array();
        $crmid = $request->get('return_id');
        $adb = PearDatabase::getInstance();

        $sql1 = 'DELETE FROM vtiger_pdfmaker_images WHERE crmid=?';
        $adb->pquery($sql1, array($crmid));

        $sql2 = 'INSERT INTO vtiger_pdfmaker_images (crmid, productid, sequence, attachmentid, width, height) VALUES (?, ?, ?, ?, ?, ?)';

        $R_Data = $request->getAll();

        foreach ($R_Data as $key => $value) {
            if (strpos($key, 'img_') !== false) {
                list($bin, $productid, $sequence) = explode("_", $key);

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

                $adb->pquery($sql2, array($crmid, $productid, $sequence, $value, $width, $height));
            }
        }

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }
}