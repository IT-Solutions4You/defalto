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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Используется, если у задачи не указаны плановые часы.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Сколько часов должна представлять в расчёте задача без оценки и полных дат.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Задачи с этими статусами считаются полностью выполненными.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Задачи с этими статусами не учитываются в прогрессе проекта.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Как рассчитывается прогресс проекта',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Более крупные задачи** сильнее влияют на прогресс проекта.

Сначала используются **плановые часы**. Если их нет, используются даты задачи и часы на календарный день. Без полных дат используются **резервные часы**.

**Завершённые задачи** считаются выполненными. **Исключённые задачи** не учитываются. Остальные используют указанный прогресс.

Прогресс обновляется при изменении задач. Его можно изменить вручную, но **следующее изменение задачи пересчитает его**.

Чтобы остановить автоматические обновления, **отключите это действие или процесс**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Рассчитать прогресс проекта',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Часов на календарный день',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Часы для задач без оценки и дат',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Завершённые статусы (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Исключённые статусы',

    'LBL_TASK_ASSIGNEE' => 'Исполнитель',
    'CV_ALL' => 'Все задачи проекта',
    'SINGLE_ProjectTask'           => "Задач проекта",
    'LBL_ADD_RECORD'               => "Добавить задач проекта",
    'LBL_RECORDS_LIST'             => "Списка задач проекта",
    'LBL_PROJECT_TASK_INFORMATION' => "Подробности задач проекта",
    'LBL_PROJECTS_LIST'            => "Список проектов",
    'LBL_TASKS_LIST'               => "Список задач",
    'LBL_MILESTONES_LIST'          => "Вехи Список",
    'Project Task Name'            => "Имя задач проекта",
    'Project Task No'              => "Не задач проекта нет",
    'Project Task Number'          => "Число задач проекта",
    'Status'                       => "Статус",
    'Priority'                     => "Приоритет",
    'Progress'                     => "Прогресс",
    'Type'                         => "Тип",
    'Worked Hours'                 => "Отработанных часов",
    'Start Date'                   => "Дата начала",
    'End Date'                     => "Дата окончания",
    'Related to'                   => "Связанный С",
    'administrative'               => 'Административный',
    'operative'                    => 'Оперативный',
    'other'                        => 'Другой',
    'low'                          => 'Низкий',
    'normal'                       => 'Нормальный',
    'high'                         => 'Высокий',
    'Created Time'                 => "Время создания",
    'Modified Time'                => "Времени изменения",
    'description'                  => "Описание",
    'Assigned To'                  => "Назначено",
    'Open'                         => "Открыть",
    'In Progress'                  => "В процессе",
    'Completed'                    => "Завершенный",
    'Deferred'                     => "Отложенный",
    'Canceled'                     => "Отменен",
    'LBL_NO_DATE_VALUE_MSG'        => 'или Задачи проекта не имеют начала и / или окончания',
];