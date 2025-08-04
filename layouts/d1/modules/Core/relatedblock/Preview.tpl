{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="h-main containerRelatedBlockEdit p-3">
    <div class="bg-body rounded">
        <div class="container-fluid border-bottom">
            <div class="row">
                <div class="col p-3">
                    <div class="fs-4">{vtranslate('LBL_RELATED_BLOCK_PREVIEW', $QUALIFIED_MODULE)}</div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row py-3">
                <div class="col-lg-3">{vtranslate('LBL_CONTENT', $QUALIFIED_MODULE)}</div>
                <div class="col-lg">
                    <div class="p-3">
                        <iframe class="ratio ration-16x9 h-50vh" src="{$IFRAME_URL}" sandbox="" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>