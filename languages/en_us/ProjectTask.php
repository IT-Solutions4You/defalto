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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Used when the task has no planned hours.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'How many hours a task without an estimate and complete dates should represent in the calculation.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Tasks in these statuses are considered fully completed.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Tasks in these statuses are not included in project progress.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'How project progress is calculated',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Larger tasks** have a greater effect on project progress.

The calculation uses **Planned Hours** first. If these are missing, it uses task dates and Hours per calendar day. Without complete dates, it uses the **fallback hours**.

**Completed tasks** count as finished. **Excluded tasks** do not count. Other tasks use their entered Progress.

Progress updates when tasks change. You can edit it manually, but the **next task change will recalculate it**.

To stop automatic updates, **disable this action or the workflow**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Calculate project progress',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Hours per calendar day',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Hours for tasks without an estimate or dates',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Completed statuses (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Excluded statuses',

    'LBL_TASK_ASSIGNEE' => 'Assignee',
    'CV_ALL' => 'All Project Tasks',
    'SINGLE_ProjectTask' => 'Project Task',
    'LBL_ADD_RECORD' => 'Add Project Task',
    'LBL_RECORDS_LIST' => 'Project Task List',
    'LBL_PROJECT_TASK_INFORMATION' => 'Project Task Details',
    'LBL_PROJECTS_LIST' => 'Projects List',
    'LBL_TASKS_LIST' => 'Tasks List',
    'LBL_MILESTONES_LIST' => 'Milestones List',
    'Project Task Name' => 'Project Task Name',
    'Project Task No' => 'Project Task No',
    'Project Task Number' => 'Project Task Number',
    'Status' => 'Status',
    'Priority' => 'Priority',
    'Progress' => 'Progress',
    'Type' => 'Type',
    'Worked Hours' => 'Worked Hours',
    'Start Date' => 'Start Date',
    'End Date' => 'End Date',
    'Related to' => 'Related To',
    'administrative' => 'Administrative',
    'operative' => 'Operative',
    'other' => 'Other',
    'low' => 'Low',
    'normal' => 'Normal',
    'high' => 'High',
    'Created Time' => 'Created Time',
    'Modified Time' => 'Modified Time',
    'description' => 'Description',
    'Assigned To' => 'Assigned To',
    'Open' => 'Open',
    'In Progress' => 'In Progress',
    'Completed' => 'Completed',
    'Deferred' => 'Deferred',
    'Canceled' => 'Canceled',
    'LBL_NO_DATE_VALUE_MSG' => 'or Project Tasks do not have Start and/or End Date',
    'Task Name' => 'Task Name',
    'Task Number' => 'Task Number',
    'Milestone' => 'Milestone',
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
    'Planed Hours' => 'Planed Hours',
    'Task No' => 'Task No',
];
$jsLanguageStrings = [
];