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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Se folosește când sarcina nu are ore planificate.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Câte ore trebuie să reprezinte în calcul o sarcină fără estimare și date complete.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Sarcinile cu aceste stări sunt considerate complet finalizate.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Sarcinile cu aceste stări nu sunt incluse în progresul proiectului.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Cum se calculează progresul proiectului',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Sarcinile mai mari** au o influență mai mare asupra progresului proiectului.

Calculul folosește mai întâi **orele planificate**. Dacă lipsesc, folosește datele sarcinii și orele pe zi calendaristică. Fără date complete, folosește **orele de rezervă**.

**Sarcinile finalizate** sunt considerate terminate. Cele **excluse** nu se iau în calcul. Celelalte folosesc progresul introdus.

Progresul se actualizează când se schimbă sarcinile. Îl puteți modifica manual, dar **următoarea schimbare a unei sarcini îl va recalcula**.

Pentru a opri actualizările automate, **dezactivați această acțiune sau procesul**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Calculează progresul proiectului',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Ore pe zi calendaristică',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Ore pentru sarcini fără estimare și date',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Stări finalizate (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Stări excluse',

    'LBL_TASK_ASSIGNEE' => 'Responsabil',
    'CV_ALL' => 'Toate sarcinile proiectului',
    'SINGLE_ProjectTask'           => "Activitate de proiect",
    'LBL_ADD_RECORD'               => "Adauga echipei de proiect",
    'LBL_RECORDS_LIST'             => "Project Task List",
    'LBL_PROJECT_TASK_INFORMATION' => "Detalii proiect de lucru",
    'LBL_PROJECTS_LIST'            => "Lista proiectelor",
    'LBL_TASKS_LIST'               => "Lista de sarcini",
    'LBL_MILESTONES_LIST'          => "Lista Repere",
    'Project Task Name'            => "Sarcina Denumirea proiectului",
    'Project Task No'              => "Proiectul Sarcina Nu",
    'Project Task Number'          => "Numărul de activități de proiect",
    'Status'                       => "Starea",
    'Priority'                     => "Prioritate",
    'Progress'                     => "Progres",
    'Type'                         => "Tip",
    'Worked Hours'                 => "Ore lucrate",
    'Start Date'                   => "Data de începere",
    'End Date'                     => "Data de încheiere",
    'Related to'                   => "Legate de",
    'administrative'               => 'Administrativ',
    'operative'                    => 'Operativ',
    'other'                        => 'Alte',
    'low'                          => 'Scăzut',
    'normal'                       => 'Normal',
    'high'                         => 'Mare',
    'Created Time'                 => "A creat timpul",
    'Modified Time'                => "Timp de modificare",
    'description'                  => "Descriere",
    'Assigned To'                  => "Atribuite",
    'Open'                         => "Deschide",
    'In Progress'                  => "În curs de desfășurare",
    'Completed'                    => "Terminat",
    'Deferred'                     => "Amânat",
    'Canceled'                     => "Anulat",
    'LBL_NO_DATE_VALUE_MSG'        => 'sau sarcini de proiect nu au Start și / sau Data de încheiere',
];