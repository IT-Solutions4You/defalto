{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=CUSTOM_VIEWS value=CustomView_Record_Model::getAllByGroup($MODULE)}
    {assign var=DEFAULT_VIEW value=$CUSTOM_VIEWS['Default'][0]}
    <div class="tag btn m-1 {if 'Detail' eq $VIEW}text-secondary bg-secondary bg-opacity-10{elseif $ACTIVE eq true}btn-primary active{else}text-primary bg-primary bg-opacity-10{/if}"
         title="{$TAG_MODEL->getName()}" data-cv-id="{$DEFAULT_VIEW->getId()}" data-type="{$TAG_MODEL->getType()}" data-id="{$TAG_MODEL->getId()}">
        <i class="activeToggleIcon fa fa-tag"></i>
        <span class="tagLabel mx-2 display-inline-block text-truncate" title="{$TAG_MODEL->getName()}">{$TAG_MODEL->getName()}</span>
        {if !$NO_EDIT}
            <i class="editTag fa fa-pencil me-2"></i>
        {/if}
        {if !$NO_DELETE}
            <i class="deleteTag fa fa-times ms-2"></i>
        {/if}
    </div>
{/strip}