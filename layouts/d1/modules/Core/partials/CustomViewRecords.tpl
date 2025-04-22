{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {if $CUSTOM_VIEWS}
        {foreach from=$CUSTOM_VIEWS item=CUSTOM_VIEW}
            <div class="dropdown-item ps-4 cursorPointer {if empty($ACTIVE_TAG) and $ACTIVE_CUSTOM_VIEW->getId() eq $CUSTOM_VIEW->getId()}text-primary bg-body-secondary fw-bold{/if}" data-open-url="{$CUSTOM_VIEW->getListViewUrl()}" data-search-element="1" data-search-value="{$CUSTOM_VIEW->get('viewname')}">
                <div class="row align-items-center">
                    <div class="col overflow-hidden text-truncate">
                        <span>{$CUSTOM_VIEW->get('viewname')}</span>
                    </div>
                    <div class="col-auto dropdown-action">
                        {if $CUSTOM_VIEW->isEditable()}
                            <a data-cv-edit-url="{$CUSTOM_VIEW->getEditUrl()}" class="btn btn-sm ms-1" title="{vtranslate('LBL_EDIT', $MODULE)}">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        {/if}
                        {if $CUSTOM_VIEW->isDeletable()}
                            <a data-cv-delete-url="{$CUSTOM_VIEW->getDeleteUrl()}" class="btn btn-sm ms-1" title="{vtranslate('LBL_DELETE', $MODULE)}">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        {/if}
                        <a class="btn btn-sm ms-1" data-cv-duplicate-url="{$CUSTOM_VIEW->getDuplicateUrl()}" title="{vtranslate('LBL_DUPLICATE', $MODULE)}">
                            <i class="fa-solid fa-copy"></i>
                        </a>
                    </div>
                </div>
            </div>
        {/foreach}
    {/if}
{/strip}