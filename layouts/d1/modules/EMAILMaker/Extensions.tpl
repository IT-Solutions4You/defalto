{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="h-main p-4">
    <div class="container-fluid bg-body rounded p-3">
        <form action="index.php" method="post" class="form-horizontal">
            <h3>{vtranslate('LBL_EXTENSIONS','EMAILMaker')}</h3>
            <p class="fieldLabel">
                <strong>{vtranslate('LBL_AVAILABLE_EXTENSIONS','EMAILMaker')}:</strong>
            </p>
            <input type="hidden" name="module" value="EMAILMaker"/>
            <input type="hidden" name="view" value=""/>
            <div>
                {foreach item=arr key=extname from=$EXTENSIONS_ARR}
                    <div class="border rounded py-3 my-3">
                        <div class="row px-3 mb-3">
                            <div class="col">
                                <h4>{vtranslate($arr.label, 'EMAILMaker')}</h4>
                            </div>
                            <div class="col-auto">
                                {if $arr.download neq ""}
                                    <a class="btn btn-outline-secondary" href="{$arr.download}">{vtranslate('LBL_DOWNLOAD', 'EMAILMaker')}</a>
                                {/if}
                            </div>
                        </div>
                        <div class="px-3">
                            <p>{vtranslate($arr.desc, 'EMAILMaker')}</p>
                            {if $arr.exinstall neq ""}
                                <p>
                                    <b>{vtranslate('LBL_INSTAL_EXT', 'EMAILMaker')}</b>
                                </p>
                                <p>{vtranslate($arr.exinstall, 'EMAILMaker')}</p>
                            {/if}
                            {if $arr.manual neq ""}
                                <p>
                                    <b>
                                        <a href="{$arr.manual}" style="cursor: pointer">{vtranslate($arr.manual_label, 'EMAILMaker')}</a>
                                    </b>
                                </p>
                            {/if}
                            {if $arr.install_info neq ""}
                                <p>
                                    <div id="install_{$extname}_info" class="fontBold {if $arr.install_info eq ""}hide{/if}">{$arr.install_info}</div>
                                </p>
                            {/if}
                            {if $arr.install neq ""}
                                <p>
                                    <button type="button" id="install_{$extname}_btn" class="btn btn-success" data-extname="{$extname}" data-url="{$arr.install}">{vtranslate('LBL_INSTALL_BUTTON', 'Install')}</button>
                                </p>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        </form>
    </div>
</div>