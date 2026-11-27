<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
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
    'Add Project Milestone' => 'Lägg till projektmilstolpe',
    'Add Project Team' => 'Lägg till projektgrupp',
    'Add Ticket' => 'Lägg till ärende',
    'LBL_PROJECT_MANAGER' => 'Projektledare',
    'LBL_SUMMARY_FILTER' => 'Filter',
    'LBL_SUMMARY_CLEAR_FILTERS' => 'Rensa filter',
    'LBL_SUMMARY_DATE_FROM' => 'Datum från',
    'LBL_SUMMARY_DATE_TO' => 'Datum till',
    'LBL_SUMMARY_ASSIGNEE' => 'Ansvarig',
    'LBL_SUMMARY_TASK_COUNT' => '%s uppgifter',
    'LBL_SUMMARY_ACTIVE_COUNT' => '%s aktiva',
    'LBL_SUMMARY_OVERDUE_COUNT' => '%s försenade',
    'LBL_SUMMARY_DATE_RANGE' => 'Från – Till',
    'LBL_SUMMARY_NO_DATES' => 'Inga datum',
    'LBL_SUMMARY_NO_MILESTONE' => 'Ingen milstolpe',
    'LBL_SUMMARY_UNASSIGNED' => 'Ej tilldelad',
    'LBL_SUMMARY_DAYS_OVERDUE' => '%s dagar försenad',
    'LBL_SUMMARY_NO_STATUS' => 'Ingen status',
    'LBL_CHART_RANGE_REMEDY' => 'Flytta datumen till projektperioden eller korrigera projektets datum.',
    'LBL_CHART_DEPENDENCY_REMEDY' => 'Flytta starten till efter föregångarens slut eller korrigera eller ta bort det felaktiga beroendet.',
    'LBL_CHART_MILESTONE_REMEDY' => 'Avsluta den angivna uppgiften senast vid milstolpens slutdatum eller korrigera slutdatumet.',
    'LBL_CHART_OVERDUE_REMEDY' => 'Uppdatera datumen eller markera uppgiften som klar om arbetet är färdigt.',
    'LBL_CHART_END' => 'Slut',
    'LBL_CHART_START' => 'Start',
    'LBL_CHART_DAY_COUNT' => 'Antal dagar',
    'LBL_MILESTONE_RANGE_HELP' => 'Milstolpen sträcker sig från den tidigaste starten för kopplade uppgifter till det sparade slutdatumet. Uppgifterna måste sluta senast den dagen. Utan startdatum visas endast slutdatumsmarkeringen. Uppgiftsdatum flyttas inte automatiskt. Utan eget datum använder milstolpen projektets planerade slut.',
    'LBL_TASK_OUTSIDE_PROJECT_DATES' => 'Uppgiftens datum måste ligga mellan projektets start och planerade slut.',
    'LBL_TASK_MILESTONE_PROJECT' => 'Den valda milstolpen måste tillhöra uppgiftens projekt.',
    'LBL_MILESTONE_BEFORE_TASK_END' => 'Milstolpen får inte sluta före datumen för de kopplade uppgifterna.',
    'LBL_TASK_AFTER_MILESTONE' => 'Uppgiftens datum får inte överskrida den kopplade milstolpens slutdatum.',
    'LBL_CHART_TABLE' => 'Uppgiftstabell',
    'LBL_CLOSED_TASK_STATUSES' => 'Avslutade uppgiftsstatusar',
    'LBL_CLOSED_TASK_STATUSES_HELP' => 'Välj statusar som inte ska markeras som försenade. Förloppsprocenten ändras inte.',
    'LBL_CHART_OVERDUE' => 'Försenad',
    'LBL_CHART_PERIOD' => 'Period',
    'LBL_CHART_SETTINGS_SETUP' => 'Diagraminställningar blir tillgängliga efter uppdatering av systemdatabasen.',
    'LBL_OUTSIDE_PROJECT_RANGE' => 'Uppgiftens datum ligger utanför projektets planerade period.',
    'LBL_DEPENDENCY_VIOLATION' => 'Uppgiften eller milstolpen börjar innan föregångaren har avslutats.',
    'LBL_DEPENDENCY_SELF' => 'En post kan inte länkas till sig själv.',
    'LBL_DEPENDENCY_DUPLICATE' => 'Detta beroende finns redan.',
    'LBL_DEPENDENCY_CYCLE' => 'Detta beroende skulle skapa en cykel.',
    'LBL_DEPENDENCY_HELP' => 'Koppla uppgifter till uppgifter eller milstolpar till milstolpar med Slut → Start. Dubbelklicka för att ta bort länken. Datum flyttas inte automatiskt.',
    'LBL_DEPENDENCY_SETUP' => 'Beroenden blir tillgängliga efter uppdatering av systemdatabasen.',
    'LBL_UNSCHEDULED_TASKS' => 'Oplanerade',
    'LBL_MISSING_START_DATE' => 'Startdatum saknas',
    'LBL_MISSING_END_DATE' => 'Slutdatum saknas',
    'LBL_INVALID_DATE_RANGE' => 'Ogiltigt datumintervall',
    'LBL_EMPTY_PROJECT_CHART' => 'Uppgifter visas på tidslinjen när de har giltiga start- och slutdatum. Lägg till en uppgift för att börja planera projektet.',
    'Milestones list' => 'Milestones Lista',
    'Add Project Task' => 'Lägg till projektuppgift',
    'Project Milestone Name' => 'Projektmilstolpsnamn',
    'Milestone Date' => 'Milstolpsdatum',
    'Project Task Name' => 'Project Task Namn',
    'Is Converted From Opportunity' => 'Omvandlat från en affärsmöjlighet',
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
    'LBL_DAY_SCALE' => 'Dag',
    'LBL_WEEK_SCALE' => 'Vecka',
    'LBL_MONTH_SCALE' => 'Månad',
    'LBL_YEAR_SCALE' => 'År',
    'CV_ALL' => 'Alla projekt',
    // Basic Strings
    'Project'                              => 'Projekt',
    'SINGLE_Project'                       => 'Projekt',
    'LBL_ADD_RECORD'                       => 'Lägg till projekt',
    'LBL_RECORDS_LIST'                     => 'Projektlista',

    // Blocks
    'LBL_PROJECT_INFORMATION'              => 'Projektuppgifter',

    //Field Labels
    'Project Name'                         => 'Projektnamn',
    'Start Date'                           => 'Startdatum',
    'Target End Date'                      => 'Målsättningför slutdatum',
    'Actual End Date'                      => 'Faktiskt slutdatum',
    'Project No'                           => 'Projektnummer',
    'Target Budget'                        => 'Målbudget',
    'Project Url'                          => 'Projekt URL',
    'Progress'                             => 'Framsteg',
    'Type'                                 => 'Typ',
    'Related to'                           => 'Relaterat till',

    //Summary Information
    'LBL_TASKS_OPEN'                       => 'Uppgifter Öppna',
    'LBL_TASKS_DUE'                        => 'Uppgifter Förfaller',
    'LBL_TASKS_COMPLETED'                  => 'Uppgifter Genomförd',
    'LBL_PEOPLE'                           => 'Människor',
    'LBL_TASKS_HIGH'                       => 'Hög prioritet',
    'LBL_TASKS_NORMAL'                     => 'Normal prioritet',
    'LBL_TASKS_LOW'                        => 'Låg prioritet',
    'LBL_TASKS_OTHER'                      => 'Andra prioriteringar',
    'LBL_SELECT_PROGRESS'                  => 'framsteg - alla',

    //Related List
    'LBL_CHARTS'                           => 'Topplista',
    'LBL_TASKS_LIST'                       => 'Uppgiftslista',
    'LBL_MILESTONES'                       => 'Milstolpar',
    'LBL_TASKS'                            => 'Uppgifter',
    'LBL_STATUS_IS'                        => 'Status är',
    'LBL_STATUS'                           => 'Status',
    'LBL_TICKET_PRIORITY'                  => 'Prioritet',

    //Summary View Widgets
    'LBL_DOWNLOAD_FILE'                    => 'Ladda ner fil',
    'LBL_MILESTONES_LIST'                  => "Milestones Lista",

    //picklist values
    'prospecting'                          => 'Prospektering',
    'in progress'                          => 'In Progress',
    'initiated'                            => 'Initierat',
    'waiting for feedback'                 => 'Väntar på återkoppling',
    'on hold'                              => 'On Hold',
    'archived'                             => 'Arkiverad',
    'completed'                            => 'Avslutat',
    'delivered'                            => 'Levereras',
    'administrative'                       => 'Administrativ',
    'operative'                            => 'Operative',
    'other'                                => 'Annat',
    'low'                                  => 'Låg',
    'normal'                               => 'Normal',
    'high'                                 => 'Hög',

    //Gantt chart
    'LBL_CHART'                            => 'Gantt-schema',
    'LBL_PROGRESS_CHART'                   => 'framsteg Gantt-schema',
    'LBL_TASK_NAME'                        => 'uppgiftsnamn',
    'LBL_START_DATE'                       => 'startdatum',
    'LBL_END_DATE'                         => 'slutdatum',
    'LBL_DURATION'                         => 'varaktighet',
    'LBL_INFO'                             => 'Info',
    'LBL_GANTT_INFO1'                      => 'Skjut på höger sida av uppgiften att utöka varaktig',
    'LBL_GANTT_INFO2'                      => 'Dra till vänster eller mitten av uppgiften att ändra start-och slutdatum.',
    'LBL_EDIT_PROJECT_TASK_STATUS_COLOR'   => 'Redigera Project Aktivitetsstatus Färg',
    'LBL_SELECT_PROJECT_TASK_STATUS_COLOR' => 'Välj Project Aktivitetsstatus Färg',
    'LBL_SELECT_STATUS'                    => 'Välj Status',
    'LBL_EDIT_COLOR'                       => 'Redigera färg',
];

$jsLanguageStrings = [
    'JS_LBL_SELECT_STATUS' => 'Välj Status',
    'JS_LBL_SELECT_PROGRESS' => 'framsteg - alla',
    'JS_START_DATE' => 'startdatum',
    'JS_END_DATE' => 'slutdatum',
    'JS_PROJECT_TASKS' => 'Uppgifter',
    'JS_TASK_NAME' => 'uppgiftsnamn',
    'JS_COLOR_SAVED_SUCESSFULLY' => 'Färg sparats',
];