<?php
/** Import or remove a user-run demonstration dataset. Default mode never boots CRM. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/Fixture.php';
$options = getopt('', ['apply','cleanup','clean-names','plan','json','help','user:','owners:','start:','tasks:','batch:']);
$allowed = ['apply','cleanup','clean-names','plan','json','help','user','owners','start','tasks','batch'];

foreach (array_slice($argv, 1) as $argument) {
    if (!str_starts_with($argument, '--') || !in_array(explode('=', substr($argument, 2), 2)[0], $allowed, true)) {
        throw new InvalidArgumentException('Unknown option. Use --help; pass values as --name=value.');
    }
}

if (isset($options['help'])) {
    echo "Plan only: php tests/gantt/import.php --start=2026-10-05 --tasks=36\n"
        . "Import:    php tests/gantt/import.php --apply --user=1 --batch=crm-demo --start=2026-10-05\n"
        . "Remove:    php tests/gantt/import.php --cleanup --apply --user=1 --batch=crm-demo\n"
        . "Names:     php tests/gantt/import.php --clean-names --apply --user=1 --batch=crm-demo\n"
        . "Optional:  --owners=1,13,5 (accessible user/group IDs), --tasks=1000, --json\n";
    exit;
}

$batch = (string)($options['batch'] ?? 'crm-demo');

if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,47}$/D', $batch)) {
    throw new InvalidArgumentException('Batch must contain 1–48 letters, numbers, underscores or hyphens.');
}

$manifestPath = __DIR__ . '/runs/' . $batch . '.json';
$manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR) : null;

if (isset($options['cleanup'], $options['clean-names'])) {
    throw new InvalidArgumentException('Choose either cleanup or clean-names.');
}

if (isset($options['clean-names']) && (!isset($options['apply']) || isset($options['plan']))) {
    if (!$manifest) {
        throw new Exception('Name cleanup requires an existing fixture manifest.');
    }

    echo 'NAME CLEANUP PLAN ONLY – ' . count($manifest['records']) . " recorded IDs; no CRM or database writes.\n";
    exit;
}

if (isset($options['cleanup']) && (!isset($options['apply']) || isset($options['plan']))) {
    if (!$manifest) {
        throw new Exception('No manifest exists for this batch. No records were removed.');
    }

    echo 'CLEANUP PLAN ONLY – ' . count($manifest['records']) . " recorded IDs; no CRM or database writes.\n";
    exit;
}
$start = (string)($options['start'] ?? $manifest['start']
    ?? (new DateTimeImmutable('monday this week', new DateTimeZone('Europe/Bratislava')))->modify('-35 days')->format('Y-m-d'));
$count = $options['tasks'] ?? $manifest['task_count'] ?? 36;

if (!ctype_digit((string)$count)) {
    throw new InvalidArgumentException('Task count must be an integer.');
}

$plan = GanttDemoFixture::createPlan($start, (int)$count);

if (!isset($options['apply']) || isset($options['plan'])) {
    echo isset($options['json']) ? json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n"
        : sprintf("PLAN ONLY – no CRM or database writes\nBatch: %s\nPeriod: %s – %s\n2 projects (one empty), %d milestones, %d tasks, %d dependencies\n3 unscheduled tasks; one-day tasks; parallel streams; one deliberate dependency warning.\nManifest: %s\n",
            $batch, $plan['start'], $plan['end'], count($plan['milestones']), count($plan['tasks']), count($plan['links']), $manifestPath);
    exit;
}

if (empty($options['user']) || !ctype_digit((string)$options['user'])) {
    throw new InvalidArgumentException('Write mode requires --user=ID of an active administrator.');
}

$adminId = (int)$options['user'];
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Runner.php';
$runner = new GanttDemoRunner($manifestPath, $batch, $plan, $manifest);

if (isset($options['clean-names'])) {
    $runner->processNameCleanup();
} elseif (isset($options['cleanup'])) {
    $runner->processCleanup();
} else {
    $runner->processImport((string)($options['owners'] ?? ''));
}
