{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="modal-footer">
        <div class="row-fluid">
            <div class="col-xs-6">
                {if $FIELD_MODEL->getProfileReadWritePermission()}
                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME)}
                {/if}
            </div>
            <div class="col-xs-6">
                <div>
                    <div class="pull-right cancelLinkContainer" style="margin-top:0px;">
                        <a class="cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </div>
                    <button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                </div>
            </div>
        </div>
    </div>
    
    
{/strip}