{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 *}
<div style="display: none;">
    {foreach item=FIELD_MODEL key=FIELD_NAME from=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']}
        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
    {/foreach}
</div>