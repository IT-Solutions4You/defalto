<?php
/** Date-only chart projection regression checks; no CRM bootstrap or database writes. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

class Vtiger_Base_Model {}
require_once dirname(__DIR__, 2) . '/modules/Project/models/Chart.php';
$chart = new Project_Chart_Model();

foreach (['UTC', 'Europe/Bratislava', 'America/New_York', 'Pacific/Kiritimati'] as $zone) {
    date_default_timezone_set($zone);

    foreach (['2026-03-29', '2026-10-25', '2026-12-31'] as $projectStart) {
        foreach ([['', ''], ['', '2027-01-20'], ['2026-01-01', ''], ['0000-00-00', '0000-00-00']] as [$start, $end]) {
            $range = $chart->getTaskRange($start, $end, $projectStart);

            if ($range !== ['start'=>$projectStart, 'end'=>$projectStart]) {
                throw new Exception('Missing dates must project as one calendar day: ' . $zone);
            }

            $milestone = $chart->getMilestoneRange(42, '2027-02-01', [
                ['milestone_id'=>42, 'start_date'=>$range['start'], 'end_date'=>$range['end']],
            ]);

            if ($milestone['start'] !== $projectStart || !$milestone['has_range']) {
                throw new Exception('Milestone must include the projected task start.');
            }
        }
    }

    foreach ([['2026-11-01', '2026-11-02'], ['2026-11-02', '2026-11-01']] as [$start, $end]) {
        if ($chart->getTaskRange($start, $end, '2026-10-01') !== ['start'=>$start, 'end'=>$end]) {
            throw new Exception('Existing complete ranges must remain unchanged.');
        }
    }

    if ($chart->getTaskRange('', '', '') !== ['start'=>'', 'end'=>'']) {
        throw new Exception('Never invent a date when project start is unavailable.');
    }
}

echo "Chart date defaults and milestone starts verified in four time zones, including DST transitions. No database writes.\n";

$tasks = [
    ['module'=>'ProjectTask', 'id'=>'task_1', 'recordid'=>1, 'text'=>'Predecessor A', 'start_date'=>'2026-10-20', 'end_date'=>'2026-10-27'],
    ['module'=>'ProjectTask', 'id'=>'task_2', 'recordid'=>2, 'text'=>'Predecessor B', 'start_date'=>'2026-10-20', 'end_date'=>'2026-10-26'],
    ['module'=>'ProjectMilestone', 'id'=>'milestone_3', 'recordid'=>3, 'text'=>'Successor', 'start_date'=>'2026-10-26', 'end_date'=>'2026-11-01'],
];
$chart->getChartLinks($tasks, [
    ['id'=>1,'sourceid'=>1,'targetid'=>3], ['id'=>2,'sourceid'=>2,'targetid'=>3], ['id'=>3,'sourceid'=>999,'targetid'=>3],
]);

if (count($tasks[2]['dependency_conflicts']) !== 2
    || $tasks[2]['dependency_conflicts'][0]['source_name'] !== 'Predecessor A'
    || $tasks[2]['dependency_conflicts'][1]['source_end'] !== '2026-10-26') {
    throw new Exception('Every visible predecessor conflict, including equality, must identify the exact dates.');
}

$range = $chart->getMilestoneRange(42, '2026-10-25', [
    ['milestone_id'=>42,'text'=>'Late task','start_date'=>'2026-10-20','end_date'=>'2026-10-27'],
    ['milestone_id'=>42,'text'=>'On time','start_date'=>'2026-10-20','end_date'=>'2026-10-25'],
    ['milestone_id'=>43,'text'=>'Other milestone','start_date'=>'2026-10-20','end_date'=>'2026-10-28'],
]);

if ($range['conflicts'] !== [['name'=>'Late task','date'=>'2026-10-27','deadline'=>'2026-10-25']]) {
    throw new Exception('Milestone warnings must identify only its own late task.');
}

echo "Exact dependency/milestone warning details verified; inaccessible endpoints are excluded.\n";
