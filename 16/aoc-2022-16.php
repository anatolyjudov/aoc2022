<?php

declare(strict_types = 1);

class Valve
{
    const START_VALVE = 'AA';

    public string $id;

    public int $rate;

    public array $tunnelsData = [];
    public array $tunnels = [];
    public array $paths = [];

    public bool $useless = true;
    public bool $start = false;

    public function __construct(string $id, int $rate, array $tunnelsData)
    {
        $this->id = $id;
        $this->rate = $rate;
        $this->tunnelsData = $tunnelsData;
        $this->start = $id === self::START_VALVE;
        $this->useless = ($rate === 0) && (!$this->start);
    }

    public function fillPaths()
    {
        //echo 'Filling paths for ' . $this->id . PHP_EOL;
        $paths = [];
        $this->fillPathGo($this, 0, $paths);
        foreach ($paths as $valveId => $pathData) {
            if ($valveId === $this->id || $valveId === self::START_VALVE || $pathData[1]->useless) continue;
            $this->paths[$valveId] = [
                'valve' => $pathData[1],
                'steps' => $pathData[0]
            ];
        }
        //echo PHP_EOL;
    }

    protected function fillPathGo(Valve $valve, $step, &$paths)
    {
        // echo $step . ' ';
        if (empty($paths[$valve->id]) || ($paths[$valve->id] > $step)) {
            $paths[$valve->id] = [$step, $valve];
        }
        if ($step === 30) return;
        foreach ($valve->tunnels as $nextValveId => $nextValve) {
            if (empty($paths[$nextValveId]) || $paths[$nextValveId][0] > ($step + 1)) {
                $this->fillPathGo($nextValve, $step + 1, $paths);
            }
        }
    }
}

$data = file_get_contents('input.txt');

$regexp = '/^Valve\s(\w{2})\D+(\d+)[^v]+valves?\s([\w,\s]+)/';
$valves = [];
foreach(explode(PHP_EOL, $data) as $valveData) {
    if (!preg_match($regexp, rtrim($valveData), $matches)) {
        die($valveData);
    }
    $valve = new Valve($matches[1], (int)$matches[2], explode(', ', $matches[3]));
    $valves[$valve->id] = $valve;
}

foreach ($valves as $valve) {
    foreach ($valve->tunnelsData as $valveId) {
        $valve->tunnels[$valves[$valveId]->id] = $valves[$valveId];
    }
}

foreach ($valves as $valve) {
    if (!$valve->useless) $valve->fillPaths();
}

printValves($valves);

$pathData = [];
$maxMinute = 30;
$walkers = array_fill(0, 2, ['pos' => $valves[Valve::START_VALVE], 'min' => 0]);
$calls = 0;
calcReleasesGo(
    $walkers,
    $maxMinute - 4 * (sizeof($walkers) - 1),
    [],
    $pathData
);
var_dump($pathData);

function calcReleasesGo(
    array $walkers,
    int $maxMinute,
    array $opened,
    &$bestPath
) {

    $currentWalker = 0;
    for ($n = 1; $n < sizeof($walkers); $n++) {
        if ($walkers[$n]['min'] <= $walkers[$currentWalker]['min']) {
            $currentWalker = $n;
        }
    }
    $valve = $walkers[$currentWalker]['pos'];
    $minute = $walkers[$currentWalker]['min'];

    // open the valve
    if (!$valve->useless && !$valve->start) {
        $minute = $minute + 1;
        $opened[$valve->id] = [
            'min' => $minute,
            'rate' => $valve->rate
        ];
    }

    // follow paths
    $hasPath = false;
    foreach ($valve->paths as $next) {
        if (($next['steps'] + $minute) >= $maxMinute) continue;
        if (!empty($opened[$next['valve']->id])) continue;
        foreach ($walkers as $walker) {
            if ($walker['pos']->id === $next['valve']->id) continue 2;
        }
        $hasPath = true;
        $walkers[$currentWalker] = [
            'pos' => $next['valve'],
            'min' => $minute + $next['steps']
        ];
        calcReleasesGo($walkers, $maxMinute, $opened, $bestPath);
    }

    if ($hasPath) return;

    if (sizeof($walkers) > 1) {
        array_splice($walkers, $currentWalker, 1);
        calcReleasesGo($walkers, $maxMinute, $opened, $bestPath);
        return;
    }

    // no paths anymore, just wait till the end
    $releaseTotal = 0;
    foreach($opened as $op) {
        $releaseTotal += ($maxMinute - $op['min']) * $op['rate'];
    }
    if (empty($bestPath['total']) || $bestPath['total'] < $releaseTotal) {
        $bestPath['total'] = $releaseTotal;
        $bestPath['history'] = $opened;
        var_dump($bestPath);
    }
}

function printValves($valves)
{
    foreach ($valves as $valve) {
        echo 'Valve ' . $valve->id . ' ' . $valve->rate . PHP_EOL;
        foreach ($valve->paths as $path) {
            echo ' -> ' . $path['valve']->id . ', ' . $path['steps'] . PHP_EOL;
        }
        echo PHP_EOL;
    }
}