{*
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
<div class="modal-dialog modal-lg h-100 my-0 py-4 d-flex align-items-end">
    <div class="modal-content">
        <link rel="stylesheet" href="{vresource_url('layouts/$LAYOUT$/modules/Tour/resources/Guide.css')}">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate($GUIDE->getLabel(), $MODULE)}
        <div class="modal-body">
            {include file=$GUIDE->getStepTemplate()|vtemplate_path:$MODULE}
        </div>
        <div class="modal-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-6 text-end">
                        <a href="{$GUIDE->getPrevStepUrl()}" class="btn btn-primary">{vtranslate('LBL_PREV_STEP', $MODULE)}</a>
                    </div>
                    <div class="col-6">
                        {if $GUIDE->isLastStep()}
                            <a href="{$GUIDE->getUrl()}" class="btn btn-primary active">{vtranslate('LBL_FINISH_GUIDE', $MODULE)}</a>
                        {else}
                            <a href="{$GUIDE->getNextStepUrl()}" class="btn btn-primary active">{vtranslate('LBL_NEXT_STEP', $MODULE)}</a>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/strip}