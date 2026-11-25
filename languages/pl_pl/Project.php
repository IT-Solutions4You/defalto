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
    'LBL_SUMMARY_FILTER' => 'Filtr',
    'LBL_SUMMARY_CLEAR_FILTERS' => 'Wyczyść filtry',
    'LBL_SUMMARY_DATE_FROM' => 'Data od',
    'LBL_SUMMARY_DATE_TO' => 'Data do',
    'LBL_SUMMARY_ASSIGNEE' => 'Osoba odpowiedzialna',
    'LBL_SUMMARY_TASK_COUNT' => '%s zadań',
    'LBL_SUMMARY_ACTIVE_COUNT' => '%s aktywnych',
    'LBL_SUMMARY_OVERDUE_COUNT' => '%s po terminie',
    'LBL_SUMMARY_PROJECT_PROGRESS' => 'Postęp projektu',
    'LBL_SUMMARY_DATE_RANGE' => 'Od – Do',
    'LBL_SUMMARY_NO_DATES' => 'Brak dat',
    'LBL_SUMMARY_NO_MILESTONE' => 'Brak kamienia milowego',
    'LBL_SUMMARY_UNASSIGNED' => 'Nieprzypisane',
    'LBL_SUMMARY_DAYS_OVERDUE' => '%s dni po terminie',
    'LBL_SUMMARY_NO_STATUS' => 'Brak statusu',
    'LBL_CHART_RANGE_REMEDY' => 'Przenieś terminy do okresu projektu lub popraw terminy projektu.',
    'LBL_CHART_DEPENDENCY_REMEDY' => 'Przesuń początek za koniec poprzednika albo popraw lub usuń błędną zależność.',
    'LBL_CHART_MILESTONE_REMEDY' => 'Zakończ wskazane zadanie do terminu kamienia milowego lub popraw ten termin.',
    'LBL_CHART_OVERDUE_REMEDY' => 'Zaktualizuj terminy lub oznacz zadanie jako ukończone, jeśli praca jest gotowa.',
    'LBL_CHART_END' => 'Koniec',
    'LBL_CHART_START' => 'Początek',
    'LBL_CHART_DAY_COUNT' => 'Liczba dni',
    'LBL_MILESTONE_RANGE_HELP' => 'Kamień milowy obejmuje okres od najwcześniejszego rozpoczęcia powiązanych zadań do terminu zapisanego w rekordzie. Zadania muszą zakończyć się najpóźniej tego dnia. Bez daty rozpoczęcia widoczny jest tylko znacznik terminu. Daty zadań nie są przesuwane automatycznie. Bez własnej daty kamień milowy używa planowanego końca projektu.',
    'LBL_TASK_OUTSIDE_PROJECT_DATES' => 'Daty zadania muszą mieścić się między początkiem a planowanym końcem projektu.',
    'LBL_TASK_MILESTONE_PROJECT' => 'Wybrany kamień milowy musi należeć do projektu zadania.',
    'LBL_MILESTONE_BEFORE_TASK_END' => 'Kamień milowy nie może kończyć się przed datami powiązanych zadań.',
    'LBL_TASK_AFTER_MILESTONE' => 'Daty zadania nie mogą przekraczać terminu powiązanego kamienia milowego.',
    'LBL_CHART_TABLE' => 'Tabela zadań',
    'LBL_CLOSED_TASK_STATUSES' => 'Zamknięte statusy zadań',
    'LBL_CLOSED_TASK_STATUSES_HELP' => 'Wybierz statusy, które nie mają być oznaczane jako opóźnione. Procent postępu pozostanie bez zmian.',
    'LBL_CHART_OVERDUE' => 'Po terminie',
    'LBL_CHART_PERIOD' => 'Okres',
    'LBL_CHART_SETTINGS_SETUP' => 'Ustawienia wykresu będą dostępne po aktualizacji bazy danych systemu.',
    'LBL_OUTSIDE_PROJECT_RANGE' => 'Terminy zadania wykraczają poza planowany okres projektu.',
    'LBL_DEPENDENCY_VIOLATION' => 'Zadanie lub kamień milowy zaczyna się przed zakończeniem poprzednika.',
    'LBL_DEPENDENCY_SELF' => 'Nie można połączyć rekordu z nim samym.',
    'LBL_DEPENDENCY_DUPLICATE' => 'Ta zależność już istnieje.',
    'LBL_DEPENDENCY_CYCLE' => 'Ta zależność utworzyłaby cykl.',
    'LBL_DEPENDENCY_HELP' => 'Łącz zadania ze sobą lub kamienie milowe ze sobą relacją Koniec → Początek. Dwukrotne kliknięcie usuwa połączenie. Daty nie są przesuwane automatycznie.',
    'LBL_DEPENDENCY_SETUP' => 'Zależności będą dostępne po aktualizacji bazy danych systemu.',
    'LBL_UNSCHEDULED_TASKS' => 'Niezaplanowane',
    'LBL_MISSING_START_DATE' => 'Brak daty rozpoczęcia',
    'LBL_MISSING_END_DATE' => 'Brak daty zakończenia',
    'LBL_INVALID_DATE_RANGE' => 'Nieprawidłowy zakres dat',
    'LBL_EMPTY_PROJECT_CHART' => 'Zadania pojawią się na osi czasu po uzupełnieniu prawidłowych dat rozpoczęcia i zakończenia. Dodaj zadanie, aby rozpocząć planowanie projektu.',
    'Milestones list' => 'Lista kamieni milowych',
    'Add Project Task' => 'Dodaj zadanie projektu',
    'Project Milestone Name' => 'Nazwa etapu',
    'Milestone Date' => 'Data rozpoczęcia etapu',
    'Project Task Name' => 'Nazwa projektu Zadanie',
    'Is Converted From Opportunity' => 'Przekształcono z szansy sprzedaży',
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
    'LBL_DAY_SCALE' => 'Dzień',
    'LBL_WEEK_SCALE' => 'Tydzień',
    'LBL_MONTH_SCALE' => 'Miesiąc',
    'LBL_YEAR_SCALE' => 'Rok',
    'CV_ALL' => 'Wszystkie projekty',
    // Basic Strings
    'Project'                 => 'Projekt',
    'SINGLE_Project'          => 'Projekt',
    'LBL_ADD_RECORD'          => 'Dodaj Projekt',
    'LBL_RECORDS_LIST'        => 'Lista Projektów',

    // Blocks
    'LBL_PROJECT_INFORMATION' => 'Szczegóły projektu',

    //Field Labels
    'Project Name'            => 'Nazwa Projektu',
    'Start Date'              => 'Data rozpoczęcia',
    'Target End Date'         => 'Planowana data zakończenia',
    'Actual End Date'         => 'Rzeczywista data zakończenia',
    'Project No'              => 'Numer projektu',
    'Target Budget'           => 'Szacowany budżet',
    'Project Url'             => 'Adres URL do projektu',
    'Progress'                => 'Realizacja',
    'Type'                    => 'Rodzaj',
    'Related to'              => 'Związane z',

    //Summary Information
    'LBL_TASKS_OPEN'          => 'Otwarte zadania',
    'LBL_TASKS_DUE'           => 'Zadania nie zamknięte',
    'LBL_TASKS_COMPLETED'     => 'Zadania zakończone',
    'LBL_PEOPLE'              => 'Ilość uczestników',
    'LBL_TASKS_HIGH'          => 'O wysokim priorytecie',
    'LBL_TASKS_NORMAL'        => 'Normalny Priorytet',
    'LBL_TASKS_LOW'           => 'Niski priorytet',
    'LBL_TASKS_OTHER'         => 'Inne priorytety',
    'LBL_SELECT_PROGRESS'     => 'Postęp - wszystko',

    //Related List
    'LBL_CHARTS'              => 'Wykresy',

    'LBL_TASKS_LIST'                       => "Lista zadań",
    'LBL_MILESTONES'                       => "Kamienie milowe",
    'LBL_TASKS'                            => "Zadania",
    'LBL_STATUS_IS'                        => "Status jest",
    'LBL_STATUS'                           => "Status",
    'LBL_TICKET_PRIORITY'                  => "Priorytet",
    'LBL_MILESTONES_LIST'                  => "Lista kamieni milowych",
    'LBL_DOWNLOAD_FILE'                    => "Pobierz plik",

    //picklist values
    'prospecting'                          => 'Poszukiwania',
    'in progress'                          => 'W toku',
    'initiated'                            => 'Zapoczątkowany',
    'waiting for feedback'                 => 'Czekam na Komentarze',
    'on hold'                              => 'Podpięcie',
    'archived'                             => 'W archiwum',
    'completed'                            => 'Zakończony',
    'delivered'                            => 'Dostarczone',
    'administrative'                       => 'Administracyjny',
    'operative'                            => 'Operacyjny',
    'other'                                => 'Inny',
    'low'                                  => 'Niski',
    'normal'                               => 'Normalny',
    'high'                                 => 'Wysoki',

    //Gantt chart
    'LBL_CHART'                            => 'Wykres Gantta',
    'LBL_PROGRESS_CHART'                   => 'Wykres Gantta',
    'LBL_TASK_NAME'                        => 'Nazwa zadania',
    'LBL_START_DATE'                       => 'data rozpoczęcia',
    'LBL_END_DATE'                         => 'Data zakończenia',
    'LBL_DURATION'                         => 'czas trwania',
    'LBL_INFO'                             => 'Informacje',
    'LBL_GANTT_INFO1'                      => 'Ślizgają się po prawej stronie zadanie rozszerzenia długości',
    'LBL_GANTT_INFO2'                      => 'Przeciągnij w lewo lub w środku zadania, aby zmienić datę rozpoczęcia i zakończenia.',
    'LBL_EDIT_PROJECT_TASK_STATUS_COLOR'   => 'Edycja Status Kolor zadań projektu',
    'LBL_SELECT_PROJECT_TASK_STATUS_COLOR' => 'Wybierz kolor statusu zadań projektu',
    'LBL_SELECT_STATUS'                    => 'Wybierz status',
    'LBL_EDIT_COLOR'                       => 'Edycja kolorów',
];

$jsLanguageStrings = [
    'JS_LBL_SELECT_STATUS' => 'Wybierz status',
    'JS_LBL_SELECT_PROGRESS' => 'Postęp - wszystko',
    'JS_START_DATE' => 'data rozpoczęcia',
    'JS_END_DATE' => 'Data zakończenia',
    'JS_PROJECT_TASKS' => 'Zadania',
    'JS_TASK_NAME' => 'Nazwa zadania',
    'JS_COLOR_SAVED_SUCESSFULLY' => 'Kolor zapisany pomyślnie',
];