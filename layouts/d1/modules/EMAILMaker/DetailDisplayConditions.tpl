{*<!--
/*********************************************************************************
* The content of this file is subject to the EMAIL Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    {assign var=DISPLAY_CONDITION value=$EMAILMAKER_RECORD_MODEL->getConditonDisplayValue()}

    {assign var=ALL_CONDITIONS value=$DISPLAY_CONDITION['All']}
    {assign var=ANY_CONDITIONS value=$DISPLAY_CONDITION['Any']}

    {if $ALL_CONDITIONS|count eq "0" && $ANY_CONDITIONS|count eq "0"}
        <div class="p-3">{vtranslate('LBL_NO_DISPLAY_CONDITIONS_DEFINED',$MODULE)}</div>
    {else}
        <div class="p-3">
            {if $DISPLAY_CONDITION['displayed'] eq "0"}
                <p>{vtranslate('LBL_DISPLAY_CONDITIONS_YES',$MODULE)}:</p>
            {else}
                <p>{vtranslate('LBL_DISPLAY_CONDITIONS_NO',$MODULE)}:</p>
            {/if}
            <div class="row py-2">
                <div class="col-2">
                    <strong>{vtranslate('All')}:</strong>
                </div>
                <div class="col">
                    {if is_array($ALL_CONDITIONS) && !empty($ALL_CONDITIONS)}
                        {foreach item=ALL_CONDITION from=$ALL_CONDITIONS name=allCounter}
                            <div>{$ALL_CONDITION}</div>
                        {/foreach}
                    {else}
                        <div>{vtranslate('LBL_NA')}</div>
                    {/if}
                </div>
            </div>
            <div class="row py-2">
                <div class="col-2">
                    <strong class="me-2">{vtranslate('Any')}:</strong>
                </div>
                <div class="col">
                    {if is_array($ANY_CONDITIONS) && !empty($ANY_CONDITIONS)}
                        {foreach item=ANY_CONDITION from=$ANY_CONDITIONS name=anyCounter}
                            <div>{$ANY_CONDITION}</div>
                        {/foreach}
                    {else}
                        <div>{vtranslate('LBL_NA')}</div>
                    {/if}
                </div>
            </div>
        </div>
    {/if}
{/strip}