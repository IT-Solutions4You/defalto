{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
<div class="listViewTagsTemplate">
    <div id="dummyTagElement" class="hide">
        {assign var=TAG_MODEL value=Vtiger_Tag_Model::getCleanInstance()}
        {include file="Tag.tpl"|vtemplate_path:$MODULE NO_DELETE=true}
    </div>
    <div>
        <div class="editTagContainer hide">
            <input type="hidden" name="id" value=""/>
            <div class="editTagContents">
                <div>
                    <input type="text" name="tagName" value="" class="form-control"/>
                </div>
                <div>
                    <div class="checkbox my-2">
                        <label>
                            <input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
                            <input type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}"/>
                            &nbsp; {vtranslate('LBL_SHARE_TAG',$MODULE)}
                        </label>
                    </div>
                </div>
            </div>
            <div class="input-group">
                <button class="btn btn-success saveTag w-50" type="button">
                    <i class="fa fa-check"></i>
                </button>
                <button class="btn btn-danger cancelSaveTag w-50" type="button">
                    <i class="fa fa-close"></i>
                </button>
            </div>
        </div>
    </div>
</div>
{/strip}