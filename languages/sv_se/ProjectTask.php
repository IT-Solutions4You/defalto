<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Används när uppgiften saknar planerade timmar.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Hur många timmar en uppgift utan uppskattning och fullständiga datum ska motsvara i beräkningen.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Uppgifter med dessa statusar betraktas som helt slutförda.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Uppgifter med dessa statusar räknas inte med i projektets framsteg.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Så beräknas projektets framsteg',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Större uppgifter** påverkar projektets framsteg mer.

Beräkningen använder först **planerade timmar**. Om de saknas används uppgiftens datum och timmar per kalenderdag. Utan fullständiga datum används **ersättningstimmarna**.

**Slutförda uppgifter** räknas som klara. **Undantagna uppgifter** räknas inte. Övriga använder sina angivna framsteg.

Framstegen uppdateras när uppgifter ändras. Du kan ändra dem manuellt, men **nästa uppgiftsändring räknar om dem**.

Inaktivera **denna åtgärd eller processen** för att stoppa automatiska uppdateringar.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Beräkna projektets framsteg',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Timmar per kalenderdag',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Timmar för uppgifter utan uppskattning och datum',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Slutförda statusar (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Undantagna statusar',

    'LBL_TASK_ASSIGNEE' => 'Ansvarig',
    'CV_ALL' => 'Alla projektuppgifter',
    // Basic Strings
    'SINGLE_ProjectTask'           => 'Projekt Uppgift',
    'LBL_ADD_RECORD'               => 'Lägg till Projektppgifter',
    'LBL_RECORDS_LIST'             => 'Projektuppgiftslista',
    'LBL_MILESTONES_LIST'          => 'Milstolpar Lista',

    // Blocks
    'LBL_PROJECT_TASK_INFORMATION' => 'Projektuppgiftsuppgifter',
    'LBL_PROJECTS_LIST'            => 'Projektlista',
    'LBL_TASKS_LIST'               => 'Uppgiftslista',

    //Field Labels
    'Project Task Name'            => 'Project Task Namn',
    'Project Task No'              => 'Projekt Task Nej',
    'Project Task Number'          => 'Projekt Uppgift nummer',
    'Status'                       => 'Status',
    'Priority'                     => 'Prioritet',
    'Progress'                     => 'Framsteg',
    'Type'                         => 'Typ',
    'Worked Hours'                 => 'Arbetade timmar',
    'Start Date'                   => 'Startdatum',
    'End Date'                     => 'Slutdatum',
    'Related to'                   => 'Relateras till',
    'administrative'               => 'Administrativ',
    'operative'                    => 'Operative',
    'other'                        => 'Annat',
    'low'                          => 'Låg',
    'normal'                       => 'Normal',
    'high'                         => 'Hög',
    'Created Time'                 => 'Skapad Tid',
    'Modified Time'                => 'Modifierad Tid',
    'description'                  => 'Beskrivning',
    'Assigned To'                  => 'Tilldelad',
    'Open'                         => 'Öppet',
    'In Progress'                  => 'In Progress',
    'Completed'                    => 'Avslutat',
    'Deferred'                     => 'Deferred',
    'Canceled'                     => 'Canceled',
    'LBL_NO_DATE_VALUE_MSG'        => 'eller projektuppgifter inte har start och / eller slutdatum',
];