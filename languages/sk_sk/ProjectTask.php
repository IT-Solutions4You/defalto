<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Použije sa, keď úloha nemá vyplnené plánované hodiny.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Koľko hodín má vo výpočte predstavovať úloha bez odhadu a úplných dátumov.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Úlohy v týchto stavoch sa považujú za úplne hotové.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Úlohy v týchto stavoch sa do pokroku projektu nezapočítajú.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Ako sa počíta pokrok projektu',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Väčšie úlohy** majú väčší vplyv na pokrok projektu.

Najprv sa použijú **Plánované hodiny**. Ak chýbajú, použijú sa dátumy úlohy a Hodiny na kalendárny deň. Bez úplných dátumov sa použijú **náhradné hodiny**.

**Dokončené úlohy** sa počítajú ako hotové. **Vylúčené úlohy** sa nepočítajú. Pri ostatných sa použije ich vyplnený Progres.

Pokrok sa aktualizuje pri zmenách úloh. Môžete ho upraviť aj ručne, ale **ďalšia zmena úlohy ho prepočíta**.

Automatický prepočet zastavíte **vypnutím tejto akcie alebo workflow**.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Prepočítať pokrok projektu',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Hodiny na kalendárny deň',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Hodiny pre úlohy bez odhadu a dátumov',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Dokončené stavy (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Vylúčené stavy',

    'LBL_TASK_ASSIGNEE' => 'Riešiteľ',
    'CV_ALL' => 'Všetky úlohy projektu',
    'SINGLE_ProjectTask' => 'Projektová úloha',
    'LBL_ADD_RECORD' => 'Pridať projektovú úlohu',
    'LBL_RECORDS_LIST' => 'Zoznam úloh',
    'LBL_PROJECT_TASK_INFORMATION' => 'Informácie o projektovej úlohe',
    'LBL_PROJECTS_LIST' => 'Zoznam projektov',
    'LBL_TASKS_LIST' => 'Zoznam úloh',
    'Project Task Name' => 'Názov projektovej úlohy',
    'Project Task No' => 'Projektová úloha č.',
    'Status' => 'Status',
    'Priority' => 'Priority',
    'Progress' => 'Progres',
    'Type' => 'Typ úlohy',
    'Start Date' => 'Dátum začatia úlohy ',
    'End Date' => 'Dátum ukončenia úlohy',
    'Related to' => 'Projekt',
    'administrative' => 'Administratívna',
    'operative' => 'Operatívna',
    'other' => 'Iná',
    'low' => 'Nízka',
    'normal' => 'Normálna',
    'high' => 'Vysoká',
    'Created Time' => 'Čas vytvorenia',
    'Modified Time' => 'Čas úpravy',
    'description' => 'Popis',
    'Assigned To' => 'Priradené komu',
    'Open' => 'Otvorená',
    'In Progress' => 'V riešení',
    'Completed' => 'Dokončená',
    'Deferred' => 'Odložená',
    'Canceled' => 'Zrušená',
    'LBL_NO_DATE_VALUE_MSG' => 'alebo Projektové úlohy nemajú začiatok a/alebo koniec',
    'Related To' => 'Projekt',
    'Project Milestone' => 'Miľník',
    'Canceled ' => 'Zrušená',
    'Task Name' => 'Názov úlohy',
    'Task Number' => 'Číslo úlohy',
    'Milestone' => 'Míľnik',
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
    'Planed Hours' => 'Plánované hodiny',
    'Task No' => 'Úloha č.',
    'LBL_MILESTONES_LIST' => 'Zoznam míľnikov',
    'Project Task Number' => 'Číslo projektovej úlohy',
    'Worked Hours' => 'Odpracované hodiny',
];
$jsLanguageStrings = [
];