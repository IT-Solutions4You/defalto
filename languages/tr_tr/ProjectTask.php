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
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Görevde planlanan saatler belirtilmediğinde kullanılır.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Tahmini ve tam tarihleri olmayan bir görevin hesaplamada kaç saati temsil edeceği.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Bu durumlardaki görevler tamamen bitmiş kabul edilir.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Bu durumlardaki görevler proje ilerlemesine dahil edilmez.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Proje ilerlemesi nasıl hesaplanır',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Daha büyük görevler** proje ilerlemesini daha fazla etkiler.

Hesaplama önce **planlanan saatleri** kullanır. Bunlar yoksa görev tarihleri ve takvim günü başına saatler kullanılır. Tam tarihler yoksa **yedek saatler** kullanılır.

**Tamamlanan görevler** bitmiş sayılır. **Hariç tutulan görevler** hesaba katılmaz. Diğerleri girilen ilerleme değerini kullanır.

Görevler değiştiğinde ilerleme güncellenir. Elle düzenleyebilirsiniz, ancak **sonraki görev değişikliği bunu yeniden hesaplar**.

Otomatik güncellemeleri durdurmak için **bu eylemi veya süreci kapatın**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Proje ilerlemesini hesapla',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Takvim günü başına saat',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Tahmini ve tarihleri olmayan görevlerin saatleri',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Tamamlanan durumlar (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Hariç tutulan durumlar',

    'LBL_TASK_ASSIGNEE' => 'Sorumlu',
    'CV_ALL' => 'Tüm proje görevleri',
    'SINGLE_ProjectTask'           => "Proje Görevi",
    'LBL_ADD_RECORD'               => "Proje Görev ekle",
    'LBL_RECORDS_LIST'             => "Proje Görev Listesi",
    'LBL_PROJECT_TASK_INFORMATION' => "Proje Görev Detayları",
    'LBL_PROJECTS_LIST'            => "Projeler Listesi",
    'LBL_TASKS_LIST'               => "Görevler Listesi",
    'LBL_MILESTONES_LIST'          => "Kilometre Taşları Listesi",
    'Project Task Name'            => "Proje Görev Adı",
    'Project Task No'              => "Proje Görev Yok",
    'Project Task Number'          => "Proje Görev sayısı",
    'Status'                       => "Durum",
    'Priority'                     => "Öncelik",
    'Progress'                     => "Ilerleme",
    'Type'                         => "Tip",
    'Worked Hours'                 => "Çalışılan Saat",
    'Start Date'                   => "Başlangıç ​​Tarihi",
    'End Date'                     => "Bitiş Tarihi",
    'Related to'                   => "İlgili",
    'administrative'               => 'Idari',
    'operative'                    => 'Faal',
    'other'                        => 'Diğer',
    'low'                          => 'Düşük',
    'normal'                       => 'Normal',
    'high'                         => 'Yüksek',
    'Created Time'                 => "Oluşturulma Zaman",
    'Modified Time'                => "Modifiye Zaman",
    'description'                  => "Tanımlama",
    'Assigned To'                  => "Atanan",
    'Open'                         => "Açık",
    'In Progress'                  => "Devam",
    'Completed'                    => "Tamamlandı",
    'Deferred'                     => "Ertelenen",
    'Canceled'                     => "Iptal edildi",
    'LBL_NO_DATE_VALUE_MSG'        => 'veya Proje Görevleri Başlat ve / veya Bitiş Tarihi yok',
];