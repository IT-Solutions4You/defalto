<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) jpl tsolucio.
 * All Rights Reserved.
 * ********************************************************************************
 *  Language     : Español es_es
 *  Version      : 6.0.0
 *  Created Date : 2012-10-26
 *  Author       : JPL TSolucio, S. L. Joe Bordes
 *  Last change  : 2013-05-03
 *  Author       : JPL TSolucio, S. L. Joe Bordes
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
    'LBL_PROJECT_MANAGER' => 'Jefe de proyecto',
    'LBL_SUMMARY_FILTER' => 'Filtro',
    'LBL_SUMMARY_CLEAR_FILTERS' => 'Borrar filtros',
    'LBL_SUMMARY_DATE_FROM' => 'Fecha desde',
    'LBL_SUMMARY_DATE_TO' => 'Fecha hasta',
    'LBL_SUMMARY_ASSIGNEE' => 'Responsable',
    'LBL_SUMMARY_TASK_COUNT' => '%s tareas',
    'LBL_SUMMARY_ACTIVE_COUNT' => '%s activas',
    'LBL_SUMMARY_OVERDUE_COUNT' => '%s vencidas',
    'LBL_SUMMARY_PROJECT_PROGRESS' => 'Progreso del proyecto',
    'LBL_SUMMARY_DATE_RANGE' => 'Desde – Hasta',
    'LBL_SUMMARY_NO_DATES' => 'Sin fechas',
    'LBL_SUMMARY_NO_MILESTONE' => 'Sin hito',
    'LBL_SUMMARY_UNASSIGNED' => 'Sin asignar',
    'LBL_SUMMARY_DAYS_OVERDUE' => '%s días de retraso',
    'LBL_SUMMARY_NO_STATUS' => 'Sin estado',
    'LBL_CHART_RANGE_REMEDY' => 'Sitúe las fechas dentro del período del proyecto o corrija las fechas del proyecto.',
    'LBL_CHART_DEPENDENCY_REMEDY' => 'Mueva el inicio después del final del registro anterior o corrija o elimine la dependencia incorrecta.',
    'LBL_CHART_MILESTONE_REMEDY' => 'Termine la tarea indicada antes o en la fecha del hito, o corrija la fecha del hito.',
    'LBL_CHART_OVERDUE_REMEDY' => 'Actualice las fechas o marque la tarea como completada si el trabajo ha terminado.',
    'LBL_CHART_END' => 'Fin',
    'LBL_CHART_START' => 'Inicio',
    'LBL_CHART_DAY_COUNT' => 'Días',
    'LBL_MILESTONE_RANGE_HELP' => 'El hito abarca desde el inicio más temprano de las tareas vinculadas hasta el plazo guardado en su registro. Las tareas deben terminar a más tardar ese día. Sin fecha de inicio, solo se muestra el marcador del plazo. Las fechas no se desplazan automáticamente. Sin fecha propia, el hito utiliza el fin previsto del proyecto.',
    'LBL_TASK_OUTSIDE_PROJECT_DATES' => 'Las fechas de la tarea deben estar entre el inicio y el fin planificado del proyecto.',
    'LBL_TASK_MILESTONE_PROJECT' => 'El hito seleccionado debe pertenecer al proyecto de la tarea.',
    'LBL_MILESTONE_BEFORE_TASK_END' => 'El hito no puede finalizar antes de las fechas de sus tareas vinculadas.',
    'LBL_TASK_AFTER_MILESTONE' => 'Las fechas de la tarea no pueden superar el plazo del hito vinculado.',
    'LBL_CHART_TABLE' => 'Tabla de tareas',
    'LBL_CLOSED_TASK_STATUSES' => 'Estados de tareas cerradas',
    'LBL_CLOSED_TASK_STATUSES_HELP' => 'Seleccione los estados que no deben marcarse como atrasados. El porcentaje de progreso no cambiará.',
    'LBL_CHART_OVERDUE' => 'Atrasada',
    'LBL_CHART_PERIOD' => 'Periodo',
    'LBL_CHART_SETTINGS_SETUP' => 'La configuración del gráfico estará disponible tras actualizar la base de datos del sistema.',
    'LBL_OUTSIDE_PROJECT_RANGE' => 'Las fechas de la tarea quedan fuera del periodo previsto del proyecto.',
    'LBL_DEPENDENCY_VIOLATION' => 'La tarea o el hito comienza antes de que finalice su predecesor.',
    'LBL_DEPENDENCY_SELF' => 'Un registro no puede vincularse consigo mismo.',
    'LBL_DEPENDENCY_DUPLICATE' => 'Esta dependencia ya existe.',
    'LBL_DEPENDENCY_CYCLE' => 'Esta dependencia crearía un ciclo.',
    'LBL_DEPENDENCY_HELP' => 'Conecte tareas entre sí o hitos entre sí mediante Fin → Inicio. Haga doble clic para eliminar el vínculo. Las fechas no se desplazan automáticamente.',
    'LBL_DEPENDENCY_SETUP' => 'Las dependencias estarán disponibles tras actualizar la base de datos del sistema.',
    'LBL_UNSCHEDULED_TASKS' => 'Sin programar',
    'LBL_MISSING_START_DATE' => 'Falta la fecha de inicio',
    'LBL_MISSING_END_DATE' => 'Falta la fecha de fin',
    'LBL_INVALID_DATE_RANGE' => 'Intervalo de fechas no válido',
    'LBL_EMPTY_PROJECT_CHART' => 'Las tareas aparecen en la línea de tiempo cuando tienen fechas de inicio y fin válidas. Añada una tarea para empezar a planificar el proyecto.',
    'Milestones list' => 'Lista de Hitos',
    'Add Project Task' => 'Añadir tarea de proyecto',
    'Project Milestone Name' => 'Nombre Hito',
    'Milestone Date' => 'Fecha Hito',
    'Project Task Name' => 'Project Task Name',
    'Is Converted From Opportunity' => 'Convertido de una oportunidad',
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
    'LBL_DAY_SCALE' => 'Día',
    'LBL_WEEK_SCALE' => 'Semana',
    'LBL_MONTH_SCALE' => 'Mes',
    'LBL_YEAR_SCALE' => 'Año',
    'CV_ALL' => 'Todos los proyectos',
    'Project'                 => 'Proyecto',
    'SINGLE_Project'          => 'Proyecto',
    'LBL_ADD_RECORD'          => 'Añadir Proyecto',
    'LBL_RECORDS_LIST'        => 'Lista de Proyectos',
    'LBL_PROJECT_INFORMATION' => 'Detalles del Proyecto',
    'Project Name'            => 'Nombre Proyecto',
    'Start Date'              => 'Fecha inicio',
    'Target End Date'         => 'Fecha fin prevista',
    'Actual End Date'         => 'Fecha fin real',
    'Project No'              => 'Número Proyecto',
    'Target Budget'           => 'Presupuesto previsto',
    'Project Url'             => 'Url Proyecto',
    'Progress'                => 'Progreso',
    'Type'                    => 'Tipo',
    'Related to'              => 'En relación con',
    'LBL_TASKS_OPEN'          => 'Actividades Abiertas',
    'LBL_TASKS_DUE'           => 'Actividades Pendientes',
    'LBL_TASKS_COMPLETED'     => 'Actividades Terminadas',
    'LBL_PEOPLE'              => 'Personas',
    'LBL_CHARTS'              => 'Gráficos',
    'LBL_TASKS_LIST'          => 'Lista Actividades',
    'LBL_MILESTONES'          => 'Hitos',
    'LBL_TASKS'               => 'Actividades',
    'LBL_STATUS_IS'           => 'Estado es',
    'LBL_STATUS'              => 'Estado',
    'LBL_TICKET_PRIORITY'     => 'Prioridad',
    'LBL_DOWNLOAD_FILE'       => 'Descargar Fichero',
    'LBL_MILESTONES_LIST'     => 'Lista de Hitos',

    'LBL_TASKS_HIGH'                       => 'Alta Prioridad',
    'LBL_TASKS_NORMAL'                     => 'Prioridad Normal',
    'LBL_TASKS_LOW'                        => 'Prioridad baja',
    'LBL_TASKS_OTHER'                      => 'Otras prioridades',
    'LBL_SELECT_PROGRESS'                  => 'Progress - Todos',
    //picklist values
    'prospecting'                          => 'Prospección',
    'in progress'                          => 'En curso',
    'initiated'                            => 'Iniciado',
    'waiting for feedback'                 => 'Esperando Feedback',
    'on hold'                              => 'En espera',
    'archived'                             => 'Archivado',
    'completed'                            => 'Terminado',
    'delivered'                            => 'Liberado',
    'administrative'                       => 'Administrativo',
    'operative'                            => 'Operatorio',
    'other'                                => 'Otro',
    'low'                                  => 'Bajo',
    'normal'                               => 'Normal',
    'high'                                 => 'Alto',

    //Gantt chart
    'LBL_CHART'                            => 'Diagrama de Gantt',
    'LBL_PROGRESS_CHART'                   => 'Diagrama de Gantt',
    'LBL_TASK_NAME'                        => 'nombre de la tarea',
    'LBL_START_DATE'                       => 'fecha de inicio',
    'LBL_END_DATE'                         => 'fecha de finalización',
    'LBL_DURATION'                         => 'duración',
    'LBL_INFO'                             => 'Info',
    'LBL_GANTT_INFO1'                      => 'Deslice el lado derecho de la tarea de ampliar la duración',
    'LBL_GANTT_INFO2'                      => 'Arrastrar a la izquierda o en el centro de la tarea de cambiar de inicio y fecha de finalización.',
    'LBL_EDIT_PROJECT_TASK_STATUS_COLOR'   => 'Estado Editar tarea Proyecto color',
    'LBL_SELECT_PROJECT_TASK_STATUS_COLOR' => 'Seleccione Estado del Proyecto Tarea color',
    'LBL_SELECT_STATUS'                    => 'Seleccione Estado',
    'LBL_EDIT_COLOR'                       => 'Editar color',
];

$jsLanguageStrings = [
    'JS_LBL_SELECT_STATUS' => 'Seleccione Estado',
    'JS_LBL_SELECT_PROGRESS' => 'Progress - Todos',
    'JS_START_DATE' => 'fecha de inicio',
    'JS_END_DATE' => 'fecha de finalización',
    'JS_PROJECT_TASKS' => 'Actividades',
    'JS_TASK_NAME' => 'nombre de la tarea',
    'JS_COLOR_SAVED_SUCESSFULLY' => 'Color Saved Successfully',
];