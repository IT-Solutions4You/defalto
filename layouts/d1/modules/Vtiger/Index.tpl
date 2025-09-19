{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="h-main">
    <div class="container rounded bg-body">
        {if $MODULE_MODEL}
            <div class="row">
                <div class="col-12 border-bottom py-3">
                    <h3>
                        {$MODULE_MODEL->getLabel()}
                    </h3>
                </div>
                <div class="col-12 py-3">
                    <p><b>{vtranslate('Parent')}</b>: {$MODULE_MODEL->get('parent')}</p>
                    <p><b>{vtranslate('Version')}</b>: {$MODULE_MODEL->get('version')}</p>
                </div>
                <div class="col-12 border-top py-3">
                    <a class="btn btn-primary" href="{$MODULE_MODEL->getDefaultUrl()}">{vtranslate('Redirect', $QUALIFIED_MODULE)}</a>
                </div>
            </div>
        {/if}
    </div>
</div>
