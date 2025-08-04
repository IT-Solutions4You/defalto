{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div id="addEventRepeatUI" data-recurring-enabled="{if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}true{else}false{/if}">
	<div><span>{$RECURRING_INFORMATION['recurringcheck']}</span></div>
	{if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}
	<div>
		<span>{vtranslate('LBL_REPEATEVENT', $MODULE_NAME)}&nbsp;{$RECURRING_INFORMATION['repeat_frequency']}&nbsp;{vtranslate($RECURRING_INFORMATION['recurringtype'], $MODULE_NAME)}</span>
	</div>
	<div>
		<span>{$RECURRING_INFORMATION['repeat_str']}</span>
	</div>
	<div>{vtranslate('LBL_UNTIL', $MODULE)}&nbsp;&nbsp;{$RECURRING_INFORMATION['recurringenddate']}</div>
	{/if}
</div>