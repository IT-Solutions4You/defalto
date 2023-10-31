{if empty($TITLE)}
    {assign var=TITLE value='LBL_WELCOME'}
{/if}
<div class="container-fluid border-bottom p-3">
    <div class="row">
        <div class="col-sm">
            <h4>{vtranslate($TITLE, 'Install')}</h4>
        </div>
        <div class="col-sm-auto">
            <a href="https://wiki.vtiger.com/vtiger6/" target="_blank" class="fs-3">
                <i class="fa-solid fa-circle-question"></i>
            </a>
        </div>
    </div>
</div>