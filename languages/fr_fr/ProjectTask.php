<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Translated by ABOnline solutions - http://www.abo-s.com/
 * Boris CLEMENT
 * December 2013
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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Utilisé lorsque la tâche ne contient pas d’heures planifiées.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Nombre d’heures qu’une tâche sans estimation ni dates complètes doit représenter dans le calcul.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Les tâches ayant ces statuts sont considérées comme entièrement terminées.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Les tâches ayant ces statuts ne sont pas incluses dans la progression du projet.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Comment la progression du projet est calculée',
    'LBL_PROJECT_PROGRESS_CALCULATION' => 'Les **tâches les plus importantes** ont plus d’influence sur la progression du projet.

Le calcul utilise d’abord les **heures planifiées**. À défaut, il utilise les dates de la tâche et les heures par jour calendaire. Sans dates complètes, les **heures de remplacement** sont utilisées.

Les **tâches terminées** comptent comme achevées. Les **tâches exclues** ne comptent pas. Les autres utilisent leur progression saisie.

La progression se met à jour lorsque les tâches changent. Vous pouvez la modifier manuellement, mais la **prochaine modification d’une tâche la recalculera**.

**Désactivez cette action ou le processus** pour arrêter les mises à jour automatiques.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Calculer la progression du projet',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Heures par jour calendaire',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Heures pour les tâches sans estimation ni dates',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Statuts terminés (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Statuts exclus',

    'LBL_TASK_ASSIGNEE' => 'Responsable',
    'CV_ALL' => 'Toutes les tâches du projet',
    'SINGLE_ProjectTask'           => "Groupe de projet",
    'LBL_ADD_RECORD'               => "Ajouter une tâche de projet",
    'LBL_RECORDS_LIST'             => "Liste des tâches de projet",
    'LBL_PROJECT_TASK_INFORMATION' => "Détails du projet de travail",
    'LBL_PROJECTS_LIST'            => "Liste des projets",
    'LBL_TASKS_LIST'               => "Liste des tâches",
    'LBL_MILESTONES_LIST'          => "Liste des jalons",
    'Project Task Name'            => "Projet Nom de la tâche",
    'Project Task No'              => "Projet Tâche",
    'Project Task Number'          => "Numéro du projet de travail",
    'Status'                       => "Statut",
    'Priority'                     => "Priorité",
    'Progress'                     => "Progress",
    'Type'                         => "Catégorie",
    'Worked Hours'                 => "Heures travaillées",
    'Start Date'                   => "Date de début",
    'End Date'                     => "Date de fin",
    'Related to'                   => "Relatif à",
    'administrative'               => 'Administratif',
    'operative'                    => 'Opératoire',
    'other'                        => 'Autre',
    'low'                          => 'Faible',
    'normal'                       => 'Normal',
    'high'                         => 'Haut',
    'Created Time'                 => "Heure de création",
    'Modified Time'                => "Temps de modification",
    'description'                  => "Description",
    'Assigned To'                  => "Assignée à",
    'Open'                         => "Ouvert",
    'In Progress'                  => "En cours",
    'Completed'                    => "Terminé",
    'Deferred'                     => "Reportés",
    'Canceled'                     => "Annulé",
    'LBL_NO_DATE_VALUE_MSG'        => 'ou tâches de projet ne ont pas de début et / ou Date de fin',
];