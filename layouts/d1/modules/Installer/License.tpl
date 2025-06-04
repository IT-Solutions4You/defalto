<div class="container-fluid">
    <div class="row py-2 mb-3 border-bottom">
        <div class="col p-0">
            <h3>{vtranslate($EXTENSION_MODEL->getName(), $EXTENSION_MODEL->getName())}: {vtranslate('LBL_LICENSE', $QUALIFIED_MODULE)}</h3>
        </div>
    </div>
</div>
<div class="container-fluid border">
    <div class="row py-2 border-bottom bg-body-secondary fw-bold">
        <div class="col-4"></div>
        <div class="col">{vtranslate('LBL_LICENSE', $QUALIFIED_MODULE)}</div>
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
            {foreach from=$EXTENSION_MODEL->getLicenseMessages() key=MESSAGE_TYPE item=MESSAGE}
                <div class="alert alert-{$MESSAGE_TYPE} mb-2">{$MESSAGE}</div>
            {/foreach}
        </div>
    </div>
    <div class="row py-2">
        <div class="col-4 text-end fw-bold text-secondary"></div>
        <div class="col">
            <a href="/index.php?module=Installer&view=Index" class="btn btn-primary">{vtranslate('LBL_LICENSE_MANAGE', $QUALIFIED_MODULE)}</a>
        </div>
    </div>
</div>
