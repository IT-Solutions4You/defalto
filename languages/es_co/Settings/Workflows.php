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
    'LBL_PROJECT_COMPLETION_TASK' => 'Completar el proyecto cuando todas las tareas estén completadas',
    'LBL_VTProjectCompletionTask' => 'Completar el proyecto cuando todas las tareas estén completadas',
    'LBL_PROJECT_COMPLETION_TITLE' => 'Cómo funciona la finalización automática del proyecto',
    'LBL_PROJECT_COMPLETION_HELP' => 'Establece el estado de proyecto elegido cuando todas las tareas no eliminadas tienen uno de los estados seleccionados. Se requiere al menos una tarea.

Comprueba **todas las tareas no eliminadas**, incluidas las ocultas. Un progreso del 100 % no basta. Las fechas y el progreso del proyecto no cambian.

Se ejecuta **después de guardar una tarea**. Al moverla, comprueba el proyecto anterior y el nuevo. Eliminar una tarea no activa esta acción.

Detenga los cambios automáticos **desactivando esta acción o el flujo de trabajo**. Si las tareas no coinciden con los estados seleccionados, el estado del proyecto no cambia.',
    'LBL_COMPLETION_TASK_STATUSES' => 'Estados de tareas completadas',
    'LBL_COMPLETION_PROJECT_STATUS' => 'Estado resultante del proyecto',
    'LBL_PROJECT_PROGRESS_TASK' => 'Calcular el progreso del proyecto',
    'LBL_VTProjectProgressTask' => 'Calcular el progreso del proyecto',
];
$jsLanguageStrings = [
    'VTProjectCompletionTask' => 'Completar el proyecto cuando todas las tareas estén completadas',
    'VTProjectProgressTask' => 'Calcular el progreso del proyecto',
];
