{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Vtiger/views/Import.php *}

<div class='fc-overlay-modal'>
    <div class="modal-content">
        <div class="overlayHeader">
            {assign var=TITLE value=implode(' ', [vtranslate('LBL_IMPORT',$MODULE), vtranslate($FOR_MODULE,$FOR_MODULE)])}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
        </div>
        <div class="modal-body" id="landingPageDiv">
            <div class="landingPage container-fluid importServiceSelectionContainer container-fluid">
                <div class="row">
                    <div class="col-lg-12 py-3">{vtranslate('LBL_SELECT_IMPORT_FILE_FORMAT',$MODULE)}</div>
                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12" id="csvImport">
                        <div class="rounded bg-body-secondary p-4 menu-item app-item app-SALES">
                            <div class="pb-4">
                                <i class="fa fa-file-text fs-3"></i>
                            </div>
                            <div>
                                <h4>{vtranslate('LBL_CSV_FILE',$MODULE)}</h4>
                            </div>
                        </div>
                    </div>
                    {if $FOR_MODULE == 'Contacts'}
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12" id="vcfImport">
                            <div class="rounded bg-body-secondary p-4 menu-item app-item app-INVENTORY">
                                <div class="pb-4">
                                    <i class="fa fa-user fs-3"></i>
                                </div>
                                <div>
                                    <h4>{vtranslate('LBL_VCF_FILE',$MODULE)}</h4>
                                </div>
                            </div>
                        </div>
                    {elseif $FOR_MODULE == 'Calendar'}
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12" id="icsImport">
                            <div class="rounded bg-body-secondary p-4 menu-item app-item">
                                <div class="pb-4">
                                    <i class="fa fa-calendar-o fs-3"></i>
                                </div>
                                <div>
                                    <h4>{vtranslate('LBL_ICS_FILE',$MODULE)}</h4>
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
