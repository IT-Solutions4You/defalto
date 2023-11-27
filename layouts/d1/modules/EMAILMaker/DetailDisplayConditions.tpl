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
        {vtranslate('LBL_NO_DISPLAY_CONDITIONS_DEFINED',$MODULE)}
    {else}
        {if $DISPLAY_CONDITION['displayed'] eq "0"}
            {vtranslate('LBL_DISPLAY_CONDITIONS_YES',$MODULE)}
        {else}
            {vtranslate('LBL_DISPLAY_CONDITIONS_NO',$MODULE)}
        {/if}:
        <br>
        <br>
        <span><strong>{vtranslate('All')}&nbsp;:&nbsp;&nbsp;&nbsp;</strong></span>
        {if is_array($ALL_CONDITIONS) && !empty($ALL_CONDITIONS)}
            {foreach item=ALL_CONDITION from=$ALL_CONDITIONS name=allCounter}
                {if $smarty.foreach.allCounter.iteration neq 1}
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
                {/if}
                <span>{$ALL_CONDITION}</span>
                <br>
            {/foreach}
        {else}
            {vtranslate('LBL_NA')}
        {/if}
        <br>
        <span><strong>{vtranslate('Any')}&nbsp;:&nbsp;</strong></span>
        {if is_array($ANY_CONDITIONS) && !empty($ANY_CONDITIONS)}
            {foreach item=ANY_CONDITION from=$ANY_CONDITIONS name=anyCounter}
                {if $smarty.foreach.anyCounter.iteration neq 1}
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
                {/if}
                <span>{$ANY_CONDITION}</span>
                <br>
            {/foreach}
        {else}
            {vtranslate('LBL_NA')}
        {/if}
    {/if}
{/strip}