<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
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
];

$jsLanguageStrings = [];
