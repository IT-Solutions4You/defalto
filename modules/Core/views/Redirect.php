<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Redirect_View extends Vtiger_Basic_View {

    public function loginRequired(): bool
    {
        return false;
    }


    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    public function postProcess(Vtiger_Request $request, $display = true)
    {
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = (string)$request->get('mode');
        $url = $this->getUrl($mode);

        if (!empty($url)) {
            header('Location: ' . $url);
        }

        throw new Exception('Invalid mode');
    }

    public function getUrl(string $mode): string
    {
        return match ($mode) {
            'Forum' => 'https://defalto.com/forum/',
            'Blog' => 'https://defalto.com/blog/',
            'Documentation' => 'https://defalto.com/docs/user-guide/',
            'Videos' => 'https://www.youtube.com/@itsolutions4you',
            'Releases' => 'https://github.com/IT-Solutions4You/defalto/releases',
            'Facebook' => 'https://www.facebook.com/defalto.crm',
            'Youtube' => 'https://www.youtube.com/@itsolutions4you',
            default => '',
        };
    }
}
