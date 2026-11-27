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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Używane, gdy zadanie nie ma podanych planowanych godzin.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Ile godzin ma reprezentować w obliczeniu zadanie bez szacunku i pełnych dat.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Zadania w tych statusach są uznawane za całkowicie ukończone.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Zadania w tych statusach nie są uwzględniane w postępie projektu.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Jak obliczany jest postęp projektu',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Większe zadania** mają większy wpływ na postęp projektu.

Najpierw używane są **planowane godziny**. Jeśli ich brakuje, używane są daty zadania i godziny na dzień kalendarzowy. Bez pełnych dat używane są **godziny zastępcze**.

**Ukończone zadania** liczą się jako gotowe. **Wykluczone zadania** nie są liczone. Pozostałe używają wpisanego postępu.

Postęp jest aktualizowany przy zmianach zadań. Można go zmienić ręcznie, ale **następna zmiana zadania obliczy go ponownie**.

Aby zatrzymać automatyczne aktualizacje, **wyłącz tę akcję lub proces**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Oblicz postęp projektu',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Godziny na dzień kalendarzowy',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Godziny dla zadań bez szacunku i dat',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Statusy ukończone (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Statusy wykluczone',

    'LBL_TASK_ASSIGNEE' => 'Wykonawca',
    'CV_ALL' => 'Wszystkie zadania projektu',
    // Basic Strings
    'SINGLE_ProjectTask'           => 'Zadanie Projektu',
    'LBL_ADD_RECORD'               => 'Dodaj Zadanie Projektu',
    'LBL_RECORDS_LIST'             => 'Lista Zadań Projektu',
    'LBL_RECORD_SUMMARY'           => 'Podsumowanie Zadania Projektu',
    'LBL_MILESTONES_LIST'          => 'Lista Milestones',

    // Blocks
    'LBL_PROJECT_TASK_INFORMATION' => 'Szczegóły zadań projektu',
    'LBL_PROJECTS_LIST'            => 'Lista projektów',
    'LBL_TASKS_LIST'               => 'Lista zadań',

    //Field Labels
    'Project Task Name'            => 'Nazwa projektu Zadanie',
    'Project Task No'              => 'Projekt nr Zadanie',
    'Project Task Number'          => 'Numer zadania projektu',
    'Status'                       => 'Status',
    'Priority'                     => 'Priorytet',
    'Progress'                     => 'Postęp',
    'Type'                         => 'Type',
    'Worked Hours'                 => 'Przepracowane godziny',
    'Start Date'                   => 'Data rozpoczęcia',
    'End Date'                     => 'Data zakończenia',
    'Related to'                   => 'Związanych z',
    'administrative'               => 'Administracyjny',
    'operative'                    => 'Operacyjny',
    'other'                        => 'Inny',
    'low'                          => 'Niski',
    'normal'                       => 'Normalny',
    'high'                         => 'Wysoki',
    'Created Time'                 => 'Utworzono Czas',
    'Modified Time'                => 'Zmodyfikowany czas',
    'description'                  => 'Opis',
    'Assigned To'                  => 'Przypisany do',
    'Open'                         => 'Otwarte',
    'In Progress'                  => 'W toku',
    'Completed'                    => 'Zakończony',
    'Deferred'                     => 'Odroczona',
    'Canceled'                     => 'Odwołany',
    'LBL_NO_DATE_VALUE_MSG'        => 'lub Zadania projektowe nie mają rozpoczęcia i / lub zakończenia Data',
];