{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
    {assign var=CALENDAR_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Appointments')}
    {if $CALENDAR_MODULE_MODEL and $USER_PRIVILEGES_MODEL->hasModulePermission($CALENDAR_MODULE_MODEL->getId())}
        {assign var=CALENDAR_TODAY_COUNT value=$CALENDAR_MODULE_MODEL->getTodayRecordsCount()}
        {assign var=CALENDAR_TODAY_RECORD value=$CALENDAR_MODULE_MODEL->getFirstTodayRecord()}
        <li class="ms-2">
            <div class="input-group align-items-center">
                <a href="{$CALENDAR_MODULE_MODEL->getDefaultUrl()}" class="btn btn-outline-secondary text-secondary border-secondary position-relative" title="{vtranslate('Appointments','Appointments')}" aria-hidden="true">
                    <i class="fa fa-calendar"></i>
                </a>
                <a href="{$CALENDAR_MODULE_MODEL->getIconUrl()}" class="btn btn-outline-secondary text-secondary border-secondary position-relative" title="{vtranslate('Appointments','Appointments')}" aria-hidden="true">
                    <b>{date('d')}</b>
                    <small class="ms-1">{vtranslate(date('M'), 'Appointments')}</small>
                    {if !empty($CALENDAR_TODAY_COUNT)}
                        <span class="position-absolute top-0 start-100 translate-middle pe-4">
                                        <span class="badge rounded-pill bg-primary">{$CALENDAR_TODAY_COUNT}</span>
                                    </span>
                    {/if}
                </a>
            </div>
        </li>
        {if $CALENDAR_TODAY_RECORD}
            <li>
                <a href="{$CALENDAR_TODAY_RECORD->getDetailViewUrl()}" class="btn btn-outline-secondary text-secondary border-secondary" title="{$CALENDAR_TODAY_RECORD->getName()}">
                    <div class="text-start">
                        <div>
                            {$CALENDAR_TODAY_RECORD->getActivityTypeIcon()}
                            <span class="ms-2">{$CALENDAR_TODAY_RECORD->getTimes()}</span>
                        </div>
                        <div>
                            <div class="text-truncate">{$CALENDAR_TODAY_RECORD->getName()}</div>
                        </div>
                    </div>
                </a>
            </li>
        {/if}
    {/if}
{/strip}