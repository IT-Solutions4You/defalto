<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!function_exists('its4you_unsubscribeemail')) {
    function its4you_unsubscribeemail($accounts_crmid, $contacts_crmid, $url_address, $label, $leads_crmid = "")
    {
        global $site_URL;

        $url = $site_URL;
        $link = '';
        $records = [];

        if (!empty($accounts_crmid) && is_numeric($accounts_crmid)) {
            $records[] = $accounts_crmid;
        }

        if (!empty($contacts_crmid) && is_numeric($contacts_crmid)) {
            $records[] = $contacts_crmid;
        }

        if (!empty($leads_crmid) && is_numeric($leads_crmid)) {
            $records[] = $leads_crmid;
        }

        $code = md5($url);
        $small_code = substr($code, 5, 6);

        if (!empty($records)) {
            $link = sprintf(
                '<a href="%s?r=%s&c=%s">%s</a>',
                $url_address,
                implode(',', $records),
                $small_code,
                $label
            );
        }

        return $link;
    }
}