{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Thunderbird-style trust dialog — the certificate the server presents and
   one button to trust exactly this certificate; values are filled by Edit.js *}
<div class="its4you-cert-box alert alert-warning hide" style="margin-top:10px;">
    <b>{vtranslate('LBL_CERT_UNTRUSTED', $QUALIFIED_MODULE)}</b>
    <table style="margin:8px 0;">
        <tr>
            <td style="padding-right:12px;">{vtranslate('LBL_CERT_SUBJECT', $QUALIFIED_MODULE)}</td>
            <td class="its4you-cert-subject"></td>
        </tr>
        <tr>
            <td style="padding-right:12px;">{vtranslate('LBL_CERT_ISSUER', $QUALIFIED_MODULE)}</td>
            <td class="its4you-cert-issuer"></td>
        </tr>
        <tr>
            <td style="padding-right:12px;">{vtranslate('LBL_CERT_VALIDITY', $QUALIFIED_MODULE)}</td>
            <td class="its4you-cert-valid"></td>
        </tr>
        <tr>
            <td style="padding-right:12px; vertical-align:top;">{vtranslate('LBL_CERT_FINGERPRINT', $QUALIFIED_MODULE)}</td>
            <td class="its4you-cert-fingerprint" style="font-family:monospace; word-break:break-all;"></td>
        </tr>
    </table>
    <div class="its4you-hint" style="margin-bottom:8px;">{vtranslate('LBL_CERT_TRUST_HINT', $QUALIFIED_MODULE)}</div>
    <button type="button" class="btn btn-outline-secondary btn-sm its4you-cert-trust">{vtranslate('LBL_CERT_TRUST', $QUALIFIED_MODULE)}</button>
</div>
