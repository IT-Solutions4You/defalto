{if empty($TITLE)}
    {assign var=TITLE value='LBL_WELCOME'}
{/if}
<div class="container-fluid border-bottom p-3">
    <div class="row">
        <div class="col-sm">
            <span class="fs-4">{vtranslate($TITLE, $MODULE)}</span>
        </div>
        <div class="col-sm-auto">
            <a href="https://defalto.com/docs/user-guide/" target="_blank" class="fs-3">
                <i class="fa-solid fa-circle-question"></i>
            </a>
        </div>
    </div>
</div>