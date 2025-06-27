{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="h-main py-4">
    <form class="container rounded bg-body form-horizontal" name="step7" method="post" action="index.php?module=Users&action=Login">
        <input type=hidden name="username" value="admin">
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
