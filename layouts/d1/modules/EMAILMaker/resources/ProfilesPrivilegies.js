/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var EMAILMaker_ProfilesPrivilegies_Js*/
Vtiger.Class('EMAILMaker_ProfilesPrivilegies_Js', {
    view_chk_clicked: function (source_chk, edit_chk_id, delete_chk_id) {
        if (source_chk.checked == false) {
            document.getElementById(edit_chk_id).checked = false;
            document.getElementById(delete_chk_id).checked = false;
        }
    },
    other_chk_clicked: function (source_chk, detail_chk) {
        if (source_chk.checked == true) {
            document.getElementById(detail_chk).checked = true;
        }
    },
}, {
})