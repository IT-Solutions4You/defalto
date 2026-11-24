<?php
/** Database-free integrity checks for the user-run fixture. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/Fixture.php';

foreach (['UTC','Europe/Bratislava','America/New_York','Pacific/Kiritimati'] as $zone) {
    date_default_timezone_set($zone);

    foreach ([36,1000] as $count) {
        $plan = GanttDemoFixture::createPlan('2026-10-05', $count);
        $tasks = array_column($plan['tasks'], null, 'key');
        $unscheduled = array_filter($tasks, static fn(array $task): bool => $task['start'] === '' || $task['end'] === '');
        $warnings = 0;
        $milestoneLinks = 0;

        foreach ($plan['links'] as [$source,$target]) {
            if (str_starts_with($source, 'm')) {
                $milestoneLinks++;
            } elseif ($tasks[$target]['start'] <= $tasks[$source]['end']) {
                $warnings++;
            }
        }

        if (count($tasks) !== $count || count($unscheduled) !== 3 || $warnings !== 1 || $milestoneLinks !== 5
            || count($plan['milestones']) !== 7 || $plan['milestones'][6]['deadline'] !== ''
            || $plan['end'] !== '2027-03-04') {
            throw new Exception('Unexpected fixture structure in ' . $zone);
        }
    }
}

echo "Fixture verified: 36/1000 tasks, dates, deadlines, missing dates, task/milestone links and acyclic graphs in four time zones. No CRM bootstrap or database writes.\n";
