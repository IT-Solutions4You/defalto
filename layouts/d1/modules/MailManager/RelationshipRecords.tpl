{foreach from=$RELATION_RECORDS item=RELATION_RECORD}
    {$EMAIL->displayed($RELATION_RECORD->getId())}
    <li class="list-group-item">
        <div class="row">
            <div class="col-1">
            </div>
            <div class="col text-truncate">
                <a class="link-primary" target="_blank" title="{$RELATION_RECORD->getName()}" href="{$RELATION_RECORD->getDetailViewUrl()}">
                    {if $RELATION_MARGIN}<i class="bi bi-arrow-90deg-up me-2"></i>{/if}
                    <span class="me-2">{$RELATION_RECORD->getModule()->getModuleIcon()}</span>
                    <span class="me-2">{$RELATION_RECORD->getName()}</span>
                </a>
            </div>
        </div>
    </li>
{/foreach}