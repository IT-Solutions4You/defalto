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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Se usa cuando la tarea no tiene horas planificadas.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Cuántas horas debe representar en el cálculo una tarea sin estimación ni fechas completas.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Las tareas en estos estados se consideran totalmente completadas.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Las tareas en estos estados no se incluyen en el progreso del proyecto.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Cómo se calcula el progreso del proyecto',
    'LBL_PROJECT_PROGRESS_CALCULATION' => 'Las **tareas más grandes** influyen más en el progreso del proyecto.

Primero se usan las **horas planificadas**. Si faltan, se usan las fechas de la tarea y las horas por día natural. Sin fechas completas se usan las **horas de respaldo**.

Las **tareas completadas** cuentan como terminadas. Las **excluidas** no cuentan. Las demás usan el progreso introducido.

El progreso se actualiza al cambiar las tareas. Puede editarlo manualmente, pero el **siguiente cambio de una tarea lo recalculará**.

Para detener las actualizaciones automáticas, **desactive esta acción o el proceso**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Calcular el progreso del proyecto',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Horas por día natural',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Horas para tareas sin estimación ni fechas',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Estados completados (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Estados excluidos',

    'LBL_TASK_ASSIGNEE' => 'Responsable',
    'CV_ALL' => 'Todas las tareas del proyecto',
    'SINGLE_ProjectTask'           => 'Tarea de proyecto',
    'LBL_ADD_RECORD'               => 'Agregar Tarea de proyecto',
    'LBL_RECORDS_LIST'             => 'Lista de Tareas de proyecto',
    'LBL_PROJECT_TASK_INFORMATION' => 'Información de Tarea de proyecto',
    'LBL_PROJECTS_LIST'            => 'Lista de proyectos',
    'LBL_TASKS_LIST'               => 'Lista de Actividades',
    'LBL_MILESTONES_LIST'          => 'Lista de metas',

    //Field Labels
    'Project Task Name'            => 'Nombre de la Tarea de proyecto',
    'Project Task No'              => 'Id Tarea de proyecto',
    'Project Task Number'          => 'Número de la Tarea de proyecto',
    'Status'                       => 'Estado',
    'Priority'                     => 'Prioridad',
    'Progress'                     => 'Progreso',
    'Type'                         => 'Tipo',
    'Worked Hours'                 => 'Horas trabajadas',
    'Start Date'                   => 'Fecha de inicio',
    'End Date'                     => 'Fecha de fin',
    'Related to'                   => 'Proyecto',
    'administrative'               => 'Administrativa',
    'operative'                    => 'Operativa',
    'other'                        => 'Otra',
    'low'                          => 'Baja',
    'normal'                       => 'Normal',
    'high'                         => 'Alta',
    'Created Time'                 => 'Hora de creación',
    'Modified Time'                => 'Hora de modificación',
    'description'                  => 'Descripción',
    'Assigned To'                  => 'Asignado a',
    'Open'                         => 'Abierta',
    'In Progress'                  => 'En progreso',
    'Deferred'                     => 'Diferida',
    'Canceled'                     => 'Cancelada',
    'Completed'                    => 'Terminada',
    'LBL_NO_DATE_VALUE_MSG'        => 'Las tareas del proyecto no tienen fecha de inicio ni / o de término',
];