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
    'LBL_PROJECT_MANAGER' => 'مدير المشروع',
    'LBL_SUMMARY_FILTER' => 'تصفية',
    'LBL_SUMMARY_CLEAR_FILTERS' => 'مسح عوامل التصفية',
    'LBL_SUMMARY_DATE_FROM' => 'من تاريخ',
    'LBL_SUMMARY_DATE_TO' => 'إلى تاريخ',
    'LBL_SUMMARY_ASSIGNEE' => 'المسؤول',
    'LBL_SUMMARY_TASK_COUNT' => '%s مهام',
    'LBL_SUMMARY_ACTIVE_COUNT' => '%s نشطة',
    'LBL_SUMMARY_OVERDUE_COUNT' => '%s متأخرة',
    'LBL_SUMMARY_DATE_RANGE' => 'من – إلى',
    'LBL_SUMMARY_NO_DATES' => 'بدون تواريخ',
    'LBL_SUMMARY_NO_MILESTONE' => 'بدون مرحلة رئيسية',
    'LBL_SUMMARY_UNASSIGNED' => 'غير معيّن',
    'LBL_SUMMARY_DAYS_OVERDUE' => 'متأخر %s أيام',
    'LBL_SUMMARY_NO_STATUS' => 'بدون حالة',
    'LBL_CHART_RANGE_REMEDY' => 'انقل التواريخ داخل فترة المشروع أو صحح تواريخ المشروع.',
    'LBL_CHART_DEPENDENCY_REMEDY' => 'اجعل البداية بعد نهاية السجل السابق أو صحح العلاقة غير الصحيحة أو أزلها.',
    'LBL_CHART_MILESTONE_REMEDY' => 'أنهِ المهمة المذكورة بحلول موعد المرحلة أو صحح موعد المرحلة.',
    'LBL_CHART_OVERDUE_REMEDY' => 'حدّث التواريخ أو ضع علامة مكتملة إذا انتهى العمل.',
    'LBL_CHART_END' => 'النهاية',
    'LBL_CHART_START' => 'البداية',
    'LBL_CHART_DAY_COUNT' => 'عدد الأيام',
    'LBL_MILESTONE_RANGE_HELP' => 'تبدأ فترة المرحلة من أقدم تاريخ بدء لمهامها وتنتهي بالموعد المحفوظ في سجل المرحلة. يجب أن تنتهي المهام بحلول هذا الموعد. إذا لم يتوفر تاريخ بدء، يُعرض الموعد كعلامة فقط. لا تتغير مواعيد المهام تلقائيًا. إذا لم يكن للمرحلة موعد خاص، تُستخدم نهاية المشروع المخططة.',
    'LBL_TASK_OUTSIDE_PROJECT_DATES' => 'يجب أن تكون تواريخ المهمة ضمن تاريخ بدء المشروع وتاريخ انتهائه المخطط.',
    'LBL_TASK_MILESTONE_PROJECT' => 'يجب أن ينتمي المعلم المحدد إلى مشروع المهمة.',
    'LBL_MILESTONE_BEFORE_TASK_END' => 'لا يمكن أن يسبق موعد المرحلة تواريخ مهامها المرتبطة.',
    'LBL_TASK_AFTER_MILESTONE' => 'لا يمكن أن تتجاوز تواريخ المهمة الموعد النهائي للمرحلة المرتبطة.',
    'LBL_CHART_TABLE' => 'جدول المهام',
    'LBL_CLOSED_TASK_STATUSES' => 'حالات المهام المغلقة',
    'LBL_CLOSED_TASK_STATUSES_HELP' => 'اختر الحالات التي يجب ألا تعتبر متأخرة. ستبقى نسبة الإنجاز دون تغيير.',
    'LBL_CHART_OVERDUE' => 'متأخرة',
    'LBL_CHART_PERIOD' => 'الفترة',
    'LBL_CHART_SETTINGS_SETUP' => 'ستتوفر إعدادات المخطط بعد تحديث قاعدة بيانات النظام.',
    'LBL_OUTSIDE_PROJECT_RANGE' => 'تواريخ المهمة تتجاوز الفترة المخططة للمشروع.',
    'LBL_DEPENDENCY_VIOLATION' => 'تبدأ المهمة أو المرحلة قبل انتهاء السابقة.',
    'LBL_DEPENDENCY_SELF' => 'لا يمكن ربط السجل بنفسه.',
    'LBL_DEPENDENCY_DUPLICATE' => 'هذه التبعية موجودة بالفعل.',
    'LBL_DEPENDENCY_CYCLE' => 'ستؤدي هذه التبعية إلى إنشاء دورة.',
    'LBL_DEPENDENCY_HELP' => 'اربط المهام ببعضها أو المراحل ببعضها من النهاية إلى البداية. انقر نقراً مزدوجاً لحذف الرابط. لا تتغير المواعيد تلقائياً.',
    'LBL_DEPENDENCY_SETUP' => 'ستتوفر التبعيات بعد تحديث قاعدة بيانات النظام.',
    'LBL_UNSCHEDULED_TASKS' => 'غير مجدولة',
    'LBL_MISSING_START_DATE' => 'تاريخ البدء مفقود',
    'LBL_MISSING_END_DATE' => 'تاريخ الانتهاء مفقود',
    'LBL_INVALID_DATE_RANGE' => 'نطاق التواريخ غير صالح',
    'LBL_EMPTY_PROJECT_CHART' => 'تظهر المهام على المخطط الزمني عند تحديد تاريخي بدء وانتهاء صالحين. أضف مهمة لبدء تخطيط المشروع.',
    'Milestones list' => 'قائمة المعالم',
    'Add Project Task' => 'إضافة مهمة مشروع',
    'Project Milestone Name' => 'اسم حدث المشروع',
    'Milestone Date' => 'تاريخ حدث المشروع',
    'Project Task Name' => 'المشروع اسم المهمة',
    'Is Converted From Opportunity' => 'تم التحويل من فرصة',
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
    'LBL_DAY_SCALE' => 'يوم',
    'LBL_WEEK_SCALE' => 'أسبوع',
    'LBL_MONTH_SCALE' => 'شهر',
    'LBL_YEAR_SCALE' => 'سنة',
    'CV_ALL' => 'جميع المشاريع',
    // Basic Strings
    'Project'                              => 'المشاريع',
    'SINGLE_Project'                       => 'المشاريع',
    'LBL_ADD_RECORD'                       => 'اضافة مشروع',
    'LBL_RECORDS_LIST'                     => 'قائمة المشاريع',

    // Blocks
    'LBL_PROJECT_INFORMATION'              => 'تفاصيل المشروع',

    //Field Labels
    'Project Name'                         => 'اسم المشروع',
    'Start Date'                           => 'تاريخ البدء',
    'Target End Date'                      => 'تاريخ الانتهاء المتوقع',
    'Actual End Date'                      => 'تاريخ الانتهاء الفعلي',
    'Project No'                           => 'رقم المشروع',
    'Target Budget'                        => 'الميزانية المستهدفة',
    'Project Url'                          => 'رابط المشروع',
    'Progress'                             => 'التقدم',
    'Type'                                 => 'النوع',
    'Related to'                           => 'المتعلقة',

    //Summary Information
    'LBL_TASKS_OPEN'                       => 'فتح مهام',
    'LBL_TASKS_DUE'                        => 'مهام مستحقة',
    'LBL_TASKS_COMPLETED'                  => 'مهام مكتملة',
    'LBL_PEOPLE'                           => 'الاشخاص',
    'LBL_TASKS_HIGH'                       => 'عالية الأولوية',
    'LBL_TASKS_NORMAL'                     => 'الأولوية العادية',
    'LBL_TASKS_LOW'                        => 'منخفض الأولوية',
    'LBL_TASKS_OTHER'                      => 'الأولويات الأخرى',
    'LBL_SELECT_PROGRESS'                  => 'التقدم - جميع',

    //Related List
    'LBL_CHARTS'                           => 'الرسوم البيانية',
    'LBL_TASKS_LIST'                       => 'قائمة المهام',
    'LBL_MILESTONES'                       => 'المعالم',
    'LBL_TASKS'                            => 'المهام',
    'LBL_STATUS_IS'                        => 'الحالة هي',
    'LBL_STATUS'                           => 'الحلات',
    'LBL_TICKET_PRIORITY'                  => 'الافضلية',

    //Summary View Widgets
    'LBL_DOWNLOAD_FILE'                    => 'تحميل الملف',
    'LBL_MILESTONES_LIST'                  => 'قائمة المعالم',

    //picklist values
    'prospecting'                          => 'تنقيب',
    'in progress'                          => 'في التقدم',
    'initiated'                            => 'بدأت',
    'waiting for feedback'                 => 'في انتظار ردود الفعل',
    'on hold'                              => 'على عقد',
    'archived'                             => 'أرشفة',
    'completed'                            => 'الانتهاء',
    'delivered'                            => 'تسليم',
    'administrative'                       => 'إداري',
    'operative'                            => 'المنطوق',
    'other'                                => 'آخر',
    'low'                                  => 'منخفض',
    'normal'                               => 'طبيعي',
    'high'                                 => 'ارتفاع',

    //Gantt chart
    'LBL_CHART'                            => 'مخطط جانت',
    'LBL_PROGRESS_CHART'                   => 'مخطط جانت',
    'LBL_TASK_NAME'                        => 'اسم المهمة',
    'LBL_START_DATE'                       => 'تاريخ بدء',
    'LBL_END_DATE'                         => 'تاريخ نهاية',
    'LBL_DURATION'                         => 'المدة',
    'LBL_INFO'                             => 'معلومات',
    'LBL_GANTT_INFO1'                      => 'الشريحة على الجانب الأيمن من مهمة لتوسيع مدة',
    'LBL_GANTT_INFO2'                      => 'السحب على اليسار أو مركز المهمة لتغيير بداية ونهاية التاريخ.',
    'LBL_EDIT_PROJECT_TASK_STATUS_COLOR'   => 'تحرير عمل مشروع مركز اللون',
    'LBL_SELECT_PROJECT_TASK_STATUS_COLOR' => 'حدد المهام حالة المشروع اللون',
    'LBL_SELECT_STATUS'                    => 'اختر الحالة',
    'LBL_EDIT_COLOR'                       => 'تحرير اللون',
];

$jsLanguageStrings = [
    'JS_LBL_SELECT_STATUS' => 'اختر الحالة',
    'JS_LBL_SELECT_PROGRESS' => 'التقدم - جميع',
    'JS_START_DATE' => 'تاريخ بدء',
    'JS_END_DATE' => 'تاريخ نهاية',
    'JS_PROJECT_TASKS' => 'المهام',
    'JS_TASK_NAME' => 'اسم المهمة',
    'JS_COLOR_SAVED_SUCESSFULLY' => 'لون المحفوظة بنجاح',
];