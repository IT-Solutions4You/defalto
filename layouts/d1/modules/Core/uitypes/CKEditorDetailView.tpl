{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*}
{strip}
    {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
    <div id="{$MODULE}_{$VIEW}_{$FIELD_NAME}" class="h-iframe Core_CKEditor_UIType">
        <iframe class="w-100 h-100" src="index.php?module={$MODULE}&record={$RECORD->getId()}&view=Iframe&field={$FIELD_MODEL->getName()}"></iframe>
    </div>
{/strip}