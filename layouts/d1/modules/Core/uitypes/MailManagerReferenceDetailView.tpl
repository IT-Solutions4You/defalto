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
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    <div id="{$MODULE}_{$VIEW}_{$FIELD_NAME}" class="Core_MailManagerReference_UIType">
        {$FIELD_VALUE}
    </div>
{/strip}