{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="h-main py-4">
    <form class="container rounded bg-body form-horizontal" name="step7" method="post" action="index.php?module=Users&action=Login">
        <input type=hidden name="username" value="{$USERNAME}">
        <input type=hidden name="password" value="{$PASSWORD}">
        <div class="row">
            <div class="col-12 p-3">
                <div class="overflow-auto h-50vh bg-body-secondary border rounded p-3">
                    {Install_InitSchema_Model::install()}
                </div>
            </div>
            <div class="col-12 p-3 text-center border-top">
                <button type="submit" class="btn btn-primary active">{vtranslate('Finish and Login to system', $QUALIFIED_MODULE)}</button>
            </div>
        </div>
    </form>
</div>
