<?php
/*+**********************************************************************************
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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Akkor használatos, ha a feladathoz nincsenek tervezett órák megadva.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Hány órát képviseljen a számításban egy becslés és teljes dátumok nélküli feladat.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Az ilyen állapotú feladatok teljesen befejezettnek számítanak.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Az ilyen állapotú feladatok nem számítanak bele a projekt előrehaladásába.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'A projekt előrehaladásának számítása',
    'LBL_PROJECT_PROGRESS_CALCULATION' => 'A **nagyobb feladatok** jobban befolyásolják a projekt előrehaladását.

A számítás először a **tervezett órákat** használja. Ha hiányoznak, a feladat dátumait és a naptári naponkénti órákat veszi alapul. Teljes dátumok nélkül a **helyettesítő órák** érvényesek.

A **befejezett feladatok** késznek számítanak. A **kizárt feladatok** nem számítanak bele. A többi a megadott előrehaladását használja.

Az előrehaladás a feladatok változásakor frissül. Kézzel is módosítható, de a **következő feladatmódosítás újraszámítja**.

Az automatikus frissítés leállításához kapcsolja ki **ezt az akciót vagy a folyamatot**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Projekt előrehaladásának kiszámítása',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Óra naptári naponként',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Órák becslés és dátumok nélküli feladatokhoz',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Befejezett állapotok (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Kizárt állapotok',

    'LBL_TASK_ASSIGNEE' => 'Felelős',
    'CV_ALL' => 'Összes projektfeladat',
    'SINGLE_ProjectTask'           => 'Projekt Feladat',
    'LBL_ADD_RECORD'               => 'Projekt Feladat hozzáadása',
    'LBL_RECORDS_LIST'             => 'Projekt Feladatok listája',
    'LBL_PROJECT_TASK_INFORMATION' => 'Projekt Feladat adatai',
    'LBL_PROJECTS_LIST'            => 'Projects List',
    'LBL_TASKS_LIST'               => 'Tasks List',
    'LBL_MILESTONES_LIST'          => 'Mérföldkövek listája',

    //Field Labels
    'Project Task Name' => 'Projektfeladat neve',
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
    'administrative'               => 'Adminisztratív',
    'operative'                    => 'Működő',
    'other'                        => 'Más',
    'low'                          => 'Alacsony',
    'normal'                       => 'Normál',
    'high'                         => 'Nagy',
    'Created Time'                 => 'Created Time',
    'Modified Time'                => 'Modified Time',
    'description'                  => 'Description',
    'Assigned To'                  => 'Assigned To',
    'Open'                         => 'Open',
    'In Progress'                  => 'In Progress',
    'Completed'                    => 'Completed',
    'Deferred'                     => 'Deferred',
    'Canceled'                     => 'Canceled',
    'LBL_NO_DATE_VALUE_MSG'        => 'vagy Projektfeladatok nincs kezdete és / vagy vége dátum',
];