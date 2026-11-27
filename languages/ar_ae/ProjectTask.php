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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'يُستخدم عندما لا توجد ساعات مخططة للمهمة.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'عدد الساعات التي تمثلها في الحساب مهمة بدون تقدير وتواريخ كاملة.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'تُعتبر المهام بهذه الحالات مكتملة بالكامل.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'لا تُحتسب المهام بهذه الحالات ضمن تقدم المشروع.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'كيفية حساب تقدم المشروع',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**المهام الأكبر** تؤثر أكثر على تقدم المشروع.

يستخدم الحساب **الساعات المخططة** أولاً. إذا لم تكن موجودة، يستخدم تواريخ المهمة والساعات لكل يوم تقويمي. بدون تواريخ كاملة تُستخدم **الساعات البديلة**.

تُحسب **المهام المكتملة** كمنجزة بالكامل. لا تُحسب **المهام المستبعدة**. تستخدم المهام الأخرى قيمة التقدم المدخلة.

يتحدث التقدم عند تغيير المهام. يمكنك تعديله يدويًا، لكن **التغيير التالي في المهمة سيعيد حسابه**.

لإيقاف التحديثات التلقائية، **عطّل هذا الإجراء أو العملية**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'حساب تقدم المشروع',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'الساعات لكل يوم تقويمي',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'ساعات المهام بدون تقدير وتواريخ',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'حالات مكتملة (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'حالات مستبعدة',

    'LBL_TASK_ASSIGNEE' => 'المكلّف',
    'CV_ALL' => 'جميع مهام المشروع',
    // Basic Strings
    'SINGLE_ProjectTask'           => 'مهام المشروع',
    'LBL_ADD_RECORD'               => 'اضافة مهام للمشروع',
    'LBL_RECORDS_LIST'             => 'قائمة مهام المشروع',

    // Blocks
    'LBL_PROJECT_TASK_INFORMATION' => 'تفاصيل مهام المشروع',
    'LBL_PROJECTS_LIST'            => 'قائمة المشاريع',
    'LBL_TASKS_LIST'               => 'قائمة المهام',
    'LBL_MILESTONES_LIST'          => 'قائمة المعالم',

    //Field Labels
    'Project Task Name'            => 'المشروع اسم المهمة',
    'Project Task No'              => 'عدد المهام المشروع',
    'Project Task Number'          => 'عدد المهام المشروع',
    'Status'                       => 'حالة',
    'Priority'                     => 'أفضلية',
    'Progress'                     => 'تقدم',
    'Type'                         => 'نوع',
    'Worked Hours'                 => 'ساعات العمل',
    'Start Date'                   => 'بدء التسجيل',
    'End Date'                     => 'تاريخ نهاية',
    'Related to'                   => 'ذات الصلة ل',
    'administrative'               => 'إداري',
    'operative'                    => 'المنطوق',
    'other'                        => 'آخر',
    'low'                          => 'منخفض',
    'normal'                       => 'طبيعي',
    'high'                         => 'ارتفاع',
    'Created Time'                 => 'خلقت وقت',
    'Modified Time'                => 'التوقيت تعديل',
    'description'                  => 'وصف',
    'Assigned To'                  => 'تعيين ل',
    'Open'                         => 'فتح',
    'In Progress'                  => 'في التقدم',
    'Completed'                    => 'الانتهاء',
    'Deferred'                     => 'تأجيل',
    'Canceled'                     => 'ألغيت',
    'LBL_NO_DATE_VALUE_MSG'        => 'أو مهام المشروع لا يكون البدء و / أو تاريخ الانتهاء',
];