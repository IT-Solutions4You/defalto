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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Wird verwendet, wenn für die Aufgabe keine geplanten Stunden angegeben sind.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Wie viele Stunden eine Aufgabe ohne Schätzung und vollständige Termine in der Berechnung darstellen soll.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Aufgaben mit diesen Status gelten als vollständig abgeschlossen.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Aufgaben mit diesen Status werden beim Projektfortschritt nicht berücksichtigt.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Berechnung des Projektfortschritts',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Größere Aufgaben** haben mehr Einfluss auf den Projektfortschritt.

Zuerst gelten die **geplanten Stunden**. Fehlen sie, werden die Aufgabentermine und Stunden pro Kalendertag verwendet. Ohne vollständige Termine gelten die **Ersatzstunden**.

**Abgeschlossene Aufgaben** zählen als fertig. **Ausgeschlossene Aufgaben** zählen nicht. Andere Aufgaben verwenden ihren eingetragenen Fortschritt.

Der Fortschritt wird bei Aufgabenänderungen aktualisiert. Sie können ihn manuell ändern; die **nächste Aufgabenänderung berechnet ihn erneut**.

Deaktivieren Sie **diese Aktion oder den Workflow**, um automatische Aktualisierungen zu stoppen.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Projektfortschritt berechnen',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Stunden pro Kalendertag',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Stunden für Aufgaben ohne Schätzung und Termine',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Abgeschlossene Status (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Ausgeschlossene Status',

    'LBL_TASK_ASSIGNEE' => 'Bearbeiter',
    'CV_ALL' => 'Alle Projektaufgaben',
    'Assigned To' => 'zuständig',
    'Canceled' => 'abgebrochen',
    'Completed' => 'abgeschlossen',
    'Created Time' => 'erstellt',
    'Deferred' => 'aufgeschoben',
    'End Date' => 'Enddatum',
    'In Progress' => 'in Bearbeitung',
    'LBL_ADD_RECORD' => 'Projektaufgabe hinzufügen',
    'LBL_MILESTONES_LIST' => 'Liste der Meilensteine',
    'LBL_NO_DATE_VALUE_MSG' => 'oder Projektaufgaben haben kein Start- und/oder Enddatum',
    'LBL_PROJECTS_LIST' => 'Projekteliste',
    'LBL_PROJECT_TASK_INFORMATION' => 'Details zur Projektaufgabe',
    'LBL_RECORDS_LIST' => 'Liste der Projektaufgaben',
    'LBL_TASKS_LIST' => 'Aufgabenliste',
    'Modified Time' => 'geändert',
    'Open' => 'offen',
    'Priority' => 'Priorität',
    'Progress' => 'Fortschritt',
    'Project Task Name' => 'Projektaufgabenname',
    'Project Task No' => 'Projektaufgabennr.',
    'Project Task Number' => 'Projekt-Aufgaben-Nummer',
    'Related to' => 'bezogen auf',
    'SINGLE_ProjectTask' => 'Projektaufgabe',
    'Start Date' => 'Startdatum',
    'Status' => 'Status',
    'Type' => 'Typ',
    'Worked Hours' => 'Arbeitsstunden',
    'administrative' => 'administrativ',
    'description' => 'Beschreibung',
    'high' => 'hoch',
    'low' => 'niedrig',
    'normal' => 'normal',
    'operative' => 'operativ',
    'other' => 'andere',
    'Task Name' => 'Task Name',
    'Task Number' => 'Task Number',
    'Milestone' => 'Milestone',
    '10%' => '10%',
    '20%' => '20%',
    '30%' => '30%',
    '40%' => '40%',
    '50%' => '50%',
    '60%' => '60%',
    '70%' => '70%',
    '80%' => '80%',
    '90%' => '90%',
    '100%' => '100%',
    'Planed Hours' => 'Planed Hours',
    'Task No' => 'Task No',
];
$jsLanguageStrings = [
];