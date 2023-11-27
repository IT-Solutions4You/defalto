{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_BROWSE_RECORD',$MODULE)}
            <div class="modal-body">
                <div id="recordDocuments">
                    <table class="table">
                        <tr>
                            <th>{vtranslate('LBL_TITLE', $MODULE)}</th>
                            <th>{vtranslate('LBL_FILENAME', $MODULE)}</th>
                            <th>{vtranslate('LBL_FOLDER', $MODULE)}</th>
                        </tr>
                        {foreach from=$RECORDS item=RECORD}
                            <tr class="selectDocument" data-id="{$RECORD['crmid']}" data-name="{$RECORD['title']}" data-filename="{$RECORD['filename']}" data-filesize="{$RECORD['filesize']}">
                                <td>{$RECORD['title']}</td>
                                <td>{$RECORD['filename']}</td>
                                <td>{$RECORD['foldername']}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
        </div>
    </div>
{/strip}