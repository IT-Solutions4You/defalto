<?php
/** Deterministic, database-free Gantt demonstration data. */
class GanttDemoFixture
{
    public static function createPlan(string $start, int $taskCount = 36): array
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $start, new DateTimeZone('Europe/Bratislava'));

        if (!$date || $date->format('Y-m-d') !== $start || $taskCount < 36 || $taskCount > 10000) {
            throw new InvalidArgumentException('Use a real YYYY-MM-DD start date and 36–10000 tasks.');
        }

        $day = static fn(?int $offset): string => $offset === null ? '' : $date->modify("+$offset days")->format('Y-m-d');
        $milestones = [];

        foreach ([['Analýza schválená',14], ['Návrh riešenia schválený',35], ['Konfigurácia dokončená',63],
            ['Integrácie pripravené',91], ['Akceptácia zákazníkom',119], ['Ostrá prevádzka odovzdaná',150],
            ['Míľnik bez dátumu – použije koniec projektu',null]] as $index => [$name,$deadline]) {
            $milestones[] = ['key' => 'm' . ($index + 1), 'name' => $name, 'deadline' => $day($deadline)];
        }

        // name, start offset, inclusive end offset, milestone, status, stored progress
        $rows = [
            ['Úvodný workshop a ciele projektu',0,3,'m1','Completed','100%'],
            ['Inventarizácia údajov v pôvodnom CRM',2,8,'m1','Completed','100%'],
            ['Mapovanie obchodných procesov',4,6,'m1','Completed','100%'],
            ['Návrh migračnej stratégie',9,10,'m1','Completed','100%'],
            ['Jednodňové schválenie analýzy',11,11,'m1','Completed','100%'],
            ['Návrh rolí a prístupových práv',15,20,'m2','Completed','100%'],
            ['Prototyp obchodného lievika',15,22,'m2','In Progress','60%'],
            ['Pripomienkovanie dátového modelu',21,26,'m2','Open','20%'],
            ['Návrh reportov pre vedenie',23,30,'m2','In Progress','40%'],
            ['Schválenie návrhu – čaká na zákazníka',31,35,'m2','Deferred','10%'],
            ['Základná konfigurácia prostredia',36,40,'m3','In Progress','70%'],
            ['Konfigurácia formulárov a povinných polí',41,48,'m3','Open','10%'],
            ['Čistenie a deduplikácia historických kontaktov',36,46,'m3','In Progress','50%'],
            ['Skúšobný import zákazníkov',47,55,'m3','Open','10%'],
            ['Dlhší názov úlohy: kontrola mapovania všetkých polí, oprávnení a väzieb pri prenose histórie komunikácie',49,56,'m3','Open','10%'],
            ['Interná kontrola konfigurácie',57,63,'m3','Open','10%'],
            ['Napojenie e-mailovej komunikácie',64,70,'m4','Open','10%'],
            ['Integrácia účtovného systému',65,75,'m4','Open','10%'],
            ['Test synchronizácie objednávok',71,80,'m4','Open','10%'],
            ['Opravy integračných testov',76,85,'m4','Open','10%'],
            ['Bezpečnostná a prístupová kontrola',86,91,'m4','Open','10%'],
            ['Príprava akceptačných scenárov',92,95,'m5','Open','10%'],
            ['Používateľské testovanie obchodného tímu',96,100,'m5','Open','10%'],
            ['Príprava školiacich materiálov',92,105,'m5','Open','10%'],
            ['Školenie kľúčových používateľov',106,110,'m5','Open','10%'],
            ['Akceptácia a odstránenie pripomienok',111,119,'m5','Open','10%'],
            ['Finálny migračný balík',120,125,'m6','Open','10%'],
            ['Jednodňové rozhodnutie o spustení',126,126,'m6','Open','10%'],
            ['Ostrý import a kontrola údajov',127,132,'m6','Open','10%'],
            ['Podpora tímu po spustení',133,140,'m6','Open','10%'],
            ['Odovzdanie dokumentácie a prevádzky',141,150,'m6','Open','10%'],
            ['Nenaplánované: doplniť oba termíny',null,null,'m5','Open','10%'],
            ['Nenaplánované: chýba koniec',100,null,'m5','Open','10%'],
            ['Nenaplánované: chýba začiatok',null,145,'m6','Open','10%'],
            ['Samostatná úloha mimo míľnikov – komunikácia projektu',0,140,null,'In Progress','30%'],
            ['Zrušená alternatíva integrácie',20,25,null,'Canceled','100%'],
        ];
        $tasks = [];

        foreach ($rows as $index => [$name,$from,$to,$milestone,$status,$progress]) {
            $tasks[] = ['key' => 't' . ($index + 1), 'name' => $name, 'start' => $day($from), 'end' => $day($to),
                'milestone' => $milestone, 'status' => $status, 'progress' => $progress, 'owner_slot' => $index % 5];
        }

        for ($index = count($tasks); $index < $taskCount; $index++) {
            $phase = $index % 6;
            $from = [0,15,36,64,92,120][$phase] + ($index % 10);
            $to = min($from + 1 + ($index % 6), [14,35,63,91,119,150][$phase]);
            $tasks[] = ['key' => 't' . ($index + 1), 'name' => sprintf('Rozšírený scenár %04d – kontrola dávky %d', $index + 1, $phase + 1),
                'start' => $day($from), 'end' => $day($to), 'milestone' => 'm' . ($phase + 1),
                'status' => $index % 4 === 0 ? 'In Progress' : 'Open', 'progress' => (($index % 9 + 1) * 10) . '%',
                'owner_slot' => $index % 5];
        }

        $links = [['t1','t3'],['t2','t4'],['t3','t4'],['t4','t5'],['t5','t6'],['t6','t8'],['t7','t9'],
            ['t8','t10'],['t9','t10'],['t10','t11'],['t11','t12'],['t13','t14'],['t12','t15'],['t14','t16'],
            ['t15','t16'],['t16','t17'],['t17','t19'],['t18','t20'],['t19','t21'],['t20','t21'],['t21','t22'],
            ['t22','t23'],['t24','t25'],['t23','t26'],['t25','t26'],['t26','t27'],['t27','t28'],['t28','t29'],
            ['t29','t30'],['t30','t31']];

        // Deliberate timing warning: successor starts before its predecessor has finished.
        $links[] = ['t7','t8'];

        for ($phase = 1; $phase < 6; $phase++) {
            $links[] = ['m' . $phase, 'm' . ($phase + 1)];
        }

        $plan = ['start' => $start, 'end' => $day(150), 'task_count' => $taskCount,
            'project_name' => 'Zavedenie CRM pre obchodný tím', 'milestones' => $milestones, 'tasks' => $tasks, 'links' => $links];
        self::validatePlan($plan);

        return $plan;
    }

    public static function validatePlan(array $plan): void
    {
        $milestones = array_column($plan['milestones'], null, 'key');
        $tasks = array_column($plan['tasks'], null, 'key');
        $nodes = $tasks + $milestones;

        if (count($tasks) !== $plan['task_count']) {
            throw new Exception('Duplicate task keys.');
        }

        foreach ($tasks as $task) {
            if ($task['milestone'] && !isset($milestones[$task['milestone']])) {
                throw new Exception('Task references an unknown milestone.');
            }

            foreach (['start','end'] as $field) {
                $date = $task[$field];

                if ($date !== '' && ($date < $plan['start'] || $date > $plan['end']
                    || ($task['milestone'] && $milestones[$task['milestone']]['deadline'] !== ''
                        && $date > $milestones[$task['milestone']]['deadline']))) {
                    throw new Exception('Task date outside its project or milestone deadline.');
                }
            }

            if ($task['start'] && $task['end'] && $task['start'] > $task['end']) {
                throw new Exception('Reversed task range.');
            }
        }

        $graph = $pairs = [];

        foreach ($plan['links'] as [$source,$target]) {
            if (!isset($nodes[$source], $nodes[$target]) || isset($tasks[$source]) !== isset($tasks[$target])
                || $source === $target || isset($pairs[$source . ':' . $target])) {
                throw new Exception('Invalid or duplicate dependency.');
            }

            $pending = [$target];
            $visited = [];

            while ($pending) {
                $node = array_pop($pending);

                if ($node === $source) {
                    throw new Exception('Cyclic dependency.');
                }

                if (!isset($visited[$node])) {
                    $visited[$node] = true;
                    array_push($pending, ...($graph[$node] ?? []));
                }
            }

            $pairs[$source . ':' . $target] = true;
            $graph[$source][] = $target;
        }
    }
}
