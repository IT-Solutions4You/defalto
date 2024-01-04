{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="col-lg-6 col-md-6 col-sm-6">
        <div class="record-header clearfix">
            <div class="recordBasicInfo">
                <div class="info-row">
                    <h4>
                        {if !$MODULE}
                            {assign var=MODULE value=$MODULE_NAME}
                        {/if}
                        <span class="modulename_label me-2">{vtranslate('LBL_MODULENAMES',$MODULE)}:</span>
                        <span>{vtranslate($RECORD->get('module'),$RECORD->get('module'))}</span>
                    </h4>
                </div>
            </div>
        </div>
    </div>
{/strip}