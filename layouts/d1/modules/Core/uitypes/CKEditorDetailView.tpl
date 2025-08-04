{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
    <div id="{$MODULE}_{$VIEW}_{$FIELD_NAME}" class="h-iframe Core_CKEditor_UIType">
        <iframe class="w-100 h-100" sandbox="" src="index.php?module={$MODULE}&record={$RECORD->getId()}&view=Iframe&field={$FIELD_MODEL->getName()}"></iframe>
    </div>
{/strip}