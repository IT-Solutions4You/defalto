<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_SavePDFTemplate_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $adb->setDebug(true);

        $adb->println("TRANS save pdfmaker starts");
        $adb->startTransaction();
        $S_Data = $request->getAll();
        $templateid = $request->get('templateid');
        $description = $request->get('description');
        $body = $S_Data['body'];
        $pdf_format = $request->get('pdf_format');
        $pdf_orientation = $request->get('pdf_orientation');
        $dh_first = $request->get('dh_first');
        $dh_other = $request->get('dh_other');
        $df_first = $request->get('df_first');
        $df_last = $request->get('df_last');
        $df_other = $request->get('df_other');

        if ($dh_first != '') {
            $dh_first = "1";
        } else {
            $dh_first = "0";
        }

        if ($dh_other != '') {
            $dh_other = '1';
        } else {
            $dh_other = '0';
        }

        if ($df_first != '') {
            $df_first = '1';
        } else {
            $df_first = '0';
        }
        if ($df_last != '') {
            $df_last = '1';
        } else {
            $df_last = '0';
        }
        if ($df_other != '') {
            $df_other = '1';
        } else {
            $df_other = '0';
        }

        $sql = 'update vtiger_pdfmaker set description =?, body =? where templateid =?';
        $params = [$description, $body, $templateid];
        $adb->pquery($sql, $params);

        $margin_top = $request->get('margin_top');
        if ($margin_top < 0) {
            $margin_top = 0;
        }

        $margin_bottom = $request->get('margin_bottom');
        if ($margin_bottom < 0) {
            $margin_bottom = 0;
        }

        $margin_left = $request->get('margin_left');
        if ($margin_left < 0) {
            $margin_left = 0;
        }

        $margin_right = $request->get('margin_right');
        if ($margin_right < 0) {
            $margin_right = 0;
        }

        $dec_point = $request->get('dec_point');
        $dec_decimals = $request->get('dec_decimals');
        $dec_thousands = $request->get('dec_thousands');

        if ($dec_thousands == ' ') {
            $dec_thousands = 'sp';
        }

        $header = $S_Data['header_body'];
        $footer = $S_Data['footer_body'];

        $encoding = $request->get('encoding');
        if ($encoding == '') {
            $encoding = 'auto';
        }

        $nameOfFile = $request->get('nameOfFile');

        if ($pdf_format == 'Custom') {
            $pdf_cf_width = $request->get('pdf_format_width');
            $pdf_cf_height = $request->get('pdf_format_height');
            $pdf_format = $pdf_cf_width . ';' . $pdf_cf_height;
        }

        $disp_header = base_convert($dh_first . $dh_other, 2, 10);
        $disp_footer = base_convert($df_first . $df_last . $df_other, 2, 10);

        $sql4 = 'UPDATE vtiger_pdfmaker_settings SET margin_top = ?, margin_bottom = ?, margin_left = ?, margin_right = ?, format = ?, orientation = ?, decimals = ?, decimal_point = ?, thousands_separator = ?, header = ?, footer = ?, encoding = ?, disp_header = ?, disp_footer= ? WHERE templateid = ?';
        $params4 = [
            $margin_top,
            $margin_bottom,
            $margin_left,
            $margin_right,
            $pdf_format,
            $pdf_orientation,
            $dec_decimals,
            $dec_point,
            $dec_thousands,
            $header,
            $footer,
            $encoding,
            $disp_header,
            $disp_footer,
            $templateid
        ];
        $adb->pquery($sql4, $params4);
        // ITS4YOU-END
        //ignored picklist values
        $adb->pquery('DELETE FROM vtiger_pdfmaker_ignorepicklistvalues', []);

        $ignore_picklist_values = $request->get('ignore_picklist_values');
        $pvvalues = explode(',', $ignore_picklist_values);
        foreach ($pvvalues as $value) {
            $adb->pquery('INSERT INTO vtiger_pdfmaker_ignorepicklistvalues(value) VALUES(?)', [trim($value)]);
        }
        // end ignored picklist values

        $adb->completeTransaction();
        $adb->println('TRANS save pdfmaker ends');

        $redirect = $request->get('redirect');
        if ($redirect == 'false') {
            $redirect_url = 'index.php?module=PDFMaker&view=EditFree&parenttab=Tools&applied=true&templateid=' . $templateid;

            $return_module = $request->get('return_module');
            $return_view = $request->get('return_view');

            if ($return_module != '') {
                $redirect_url .= '&return_module=' . $return_module;
            }
            if ($return_view != '') {
                $redirect_url .= '&return_view=' . $return_view;
            }

            header('Location:' . $redirect_url);
        } else {
            header('Location:index.php?module=PDFMaker&view=DetailFree&parenttab=Tools&templateid=' . $templateid);
        }
    }
}