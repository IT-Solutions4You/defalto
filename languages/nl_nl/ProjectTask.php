<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Wordt gebruikt wanneer de taak geen geplande uren heeft.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Hoeveel uur een taak zonder schatting en volledige datums in de berekening moet vertegenwoordigen.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Taken met deze statussen worden als volledig voltooid beschouwd.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Taken met deze statussen tellen niet mee voor de projectvoortgang.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Hoe projectvoortgang wordt berekend',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Grotere taken** hebben meer invloed op de projectvoortgang.

De berekening gebruikt eerst de **geplande uren**. Ontbreken die, dan gelden de taakdatums en uren per kalenderdag. Zonder volledige datums gelden de **vervangende uren**.

**Voltooide taken** tellen als klaar. **Uitgesloten taken** tellen niet mee. Andere taken gebruiken hun ingevulde voortgang.

De voortgang wordt bijgewerkt wanneer taken veranderen. U kunt deze handmatig aanpassen, maar de **volgende taakwijziging berekent deze opnieuw**.

Schakel **deze actie of het proces** uit om automatische updates te stoppen.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Projectvoortgang berekenen',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Uren per kalenderdag',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Uren voor taken zonder schatting en datums',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Voltooide statussen (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Uitgesloten statussen',

    'LBL_TASK_ASSIGNEE' => 'Behandelaar',
    'CV_ALL' => 'Alle projecttaken',
    'SINGLE_ProjectTask'           => 'Projecttaak',
    'LBL_ADD_RECORD'               => 'Voeg Projecttaak toe',
    'LBL_RECORDS_LIST'             => 'Projecttaak List',
    'LBL_PROJECT_TASK_INFORMATION' => 'Projecttaak Informatie',
    'LBL_PROJECTS_LIST'            => 'Projects List',
    'LBL_TASKS_LIST'               => 'Tasks List',
    'LBL_MILESTONES_LIST'          => 'Mijlpalen Lijst',

    //Field Labels
    'Project Task Name' => 'Naam projecttaak',
    'Project Task No'              => 'Project Task No',
    'Project Task Number'          => 'Project Task Number',
    'Status'                       => 'Status',
    'Priority'                     => 'Priority',
    'Progress'                     => 'Progress',
    'Type'                         => 'Type',
    'Worked Hours'                 => 'Worked Hours',
    'Start Date'                   => 'Start Date',
    'End Date'                     => 'End Date',
    'Related to'                   => 'Related To',
    'administrative'               => 'Administratief',
    'operative'                    => 'Arbeider',
    'other'                        => 'Ander',
    'low'                          => 'Laag',
    'normal'                       => 'Normaal',
    'high'                         => 'Hoog',
    'Created Time'                 => 'Created Time',
    'Modified Time'                => 'Modified Time',
    'description'                  => 'Description',
    'Assigned To'                  => 'Assigned To',
    'Open'                         => 'Open',
    'In Progress'                  => 'In Progress',
    'Completed'                    => 'Completed',
    'Deferred'                     => 'Deferred',
    'Canceled'                     => 'Canceled',
    'LBL_NO_DATE_VALUE_MSG'        => 'of Projecttaken heb geen Start en / of Einddatum',
];