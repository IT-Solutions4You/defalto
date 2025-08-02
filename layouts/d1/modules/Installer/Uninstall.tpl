{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="container-fluid">
    <div class="row py-2 mb-3 border-bottom">
        <div class="col p-0">
            <h3>{vtranslate($EXTENSION_MODEL->getName(), $EXTENSION_MODEL->getName())}: {vtranslate('LBL_UNINSTALL', $QUALIFIED_MODULE)}</h3>
        </div>
    </div>
</div>
<div class="container-fluid border">
    <div class="row py-2 border-bottom bg-body-secondary fw-bold">
        <div class="col-4"></div>
        <div class="col">{vtranslate('LBL_UNINSTALL', $QUALIFIED_MODULE)}</div>
    </div>
    <div class="row py-2 border-bottom">
        <div class="col-4 text-end fw-bold text-secondary">{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}:</div>
        <div class="col">{vtranslate($EXTENSION_MODEL->getName(), $EXTENSION_MODEL->getName())}</div>
    </div>
    <div class="row py-2 border-bottom">
        <div class="col-4 text-end fw-bold text-secondary">{vtranslate('LBL_VERSION', $QUALIFIED_MODULE)}:</div>
        <div class="col">{$EXTENSION_MODEL->getVersion()}</div>
    </div>
    <div class="row py-2 border-bottom">
        <div class="col-4 text-end fw-bold text-secondary">{vtranslate('LBL_CRM_VERSION', $QUALIFIED_MODULE)}:</div>
        <div class="col">{$EXTENSION_MODEL->getCRMVersion()}</div>
    </div>
    <div class="row py-2 border-bottom">
        <div class="col-4 text-end fw-bold text-secondary">{vtranslate('LBL_CRM_URL', $QUALIFIED_MODULE)}:</div>
        <div class="col">{$EXTENSION_MODEL->getCRMUrl()}</div>
    </div>
    <div class="row py-2 border-bottom">
        <div class="col-4 text-end fw-bold text-secondary">{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}:</div>
        <div class="col">
            <div class="alert alert-danger mb-2">{vtranslate('LBL_UNINSTALL_MODULE_TABLES', $QUALIFIED_MODULE)}</div>
            <div class="alert alert-danger m-0">{vtranslate('LBL_UNINSTALL_MODULE_FILES', $QUALIFIED_MODULE)}</div>
        </div>
    </div>
    <div class="row py-2">
        <div class="col-4 text-end fw-bold text-secondary"></div>
        <div class="col">
            <form action="index.php" method="post">
                <input type="hidden" name="module" value="Installer">
                <input type="hidden" name="view" value="IndexAjax">
                <input type="hidden" name="mode" value="extensionUninstall">
                <input type="hidden" name="sourceModule" value="{$EXTENSION_MODEL->getName()}">
                <label class="form-check d-block py-2 text-secondary">
                    <input type="checkbox" class="form-check-input" name="confirmed" value="Yes">
                    <span class="form-check-label">{vtranslate('LBL_UNINSTALL_CONFIRM', $QUALIFIED_MODULE)}</span>
                </label>
                <button class="btn btn-primary" type="submit">{vtranslate('LBL_UNINSTALL', $QUALIFIED_MODULE)}</button>
            </form>
        </div>
    </div>
</div>
