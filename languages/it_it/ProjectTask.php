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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Usato quando l’attività non ha ore pianificate.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Quante ore deve rappresentare nel calcolo un’attività senza stima e date complete.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Le attività in questi stati sono considerate completamente terminate.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Le attività in questi stati non sono incluse nell’avanzamento del progetto.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Come viene calcolato l’avanzamento del progetto',
    'LBL_PROJECT_PROGRESS_CALCULATION' => 'Le **attività più grandi** influiscono maggiormente sull’avanzamento del progetto.

Il calcolo usa prima le **ore pianificate**. Se mancano, usa le date dell’attività e le ore per giorno di calendario. Senza date complete usa le **ore sostitutive**.

Le **attività completate** contano come finite. Quelle **escluse** non contano. Le altre usano l’avanzamento inserito.

L’avanzamento si aggiorna quando cambiano le attività. Può essere modificato manualmente, ma la **prossima modifica a un’attività lo ricalcolerà**.

Per fermare gli aggiornamenti automatici, **disattivare questa azione o il processo**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Calcola avanzamento progetto',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Ore per giorno di calendario',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Ore per attività senza stima e date',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Stati completati (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Stati esclusi',

    'LBL_TASK_ASSIGNEE' => 'Assegnatario',
    'CV_ALL' => 'Tutte le attività del progetto',
    'SINGLE_ProjectTask'           => "Attività del progetto",
    'LBL_ADD_RECORD'               => "Aggiungi attività di progetto",
    'LBL_RECORDS_LIST'             => "Elenco Attività del Progetto",
    'LBL_PROJECT_TASK_INFORMATION' => "Dettagli Attività del Progetto",
    'LBL_PROJECTS_LIST'            => "Lista Progetti",
    'LBL_TASKS_LIST'               => "List Attività",
    'LBL_MILESTONES_LIST'          => "Lista Stadi",
    'Project Task Name'            => "Nome attività del Progetto",
    'Project Task No'              => "Attività del Progetto N.",
    'Project Task Number'          => "Numero Attività del Progetto",
    'Status'                       => "Stato",
    'Priority'                     => "Priorità",
    'Progress'                     => "Progresso",
    'Type'                         => "Tipo",
    'Worked Hours'                 => "Ore lavorate",
    'Start Date'                   => "Data di inizio",
    'End Date'                     => "Data di fine",
    'Related to'                   => "Legati alla",
    'administrative'               => 'Amministrativo',
    'operative'                    => 'Operativo',
    'other'                        => 'Altro',
    'low'                          => 'Basso',
    'normal'                       => 'Normale',
    'high'                         => 'Alto',
    'Created Time'                 => "Tempo di creazione",
    'Modified Time'                => "Tempo Modifica",
    'description'                  => "Descrizione",
    'Assigned To'                  => "Assegnato a",
    'Open'                         => "Aprire",
    'In Progress'                  => "In corso",
    'Completed'                    => "Completato",
    'Deferred'                     => "Differite",
    'LBL_NO_DATE_VALUE_MSG'        => 'o Attività del Progetto non hanno Data di Inizio e/o Fine',
    'Canceled'                     => 'Annullata',
];