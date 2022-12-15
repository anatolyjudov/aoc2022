<?php

declare(strict_types = 1);

$data = file_get_contents('input.txt');

$sensors = $beacons = $distances = [];
foreach(explode(PHP_EOL, $data) as $sensorData) {
    preg_match('/^[^-\d]+(-?\d+)[^-\d]+(-?\d+)[^-\d]+(-?\d+)[^-\d]+(-?\d+)/', $sensorData, $n);
    $i = sizeof($sensors);
    $sensors[$i] = [(int) $n[1], (int) $n[2]];
    $beacons[$i] = [(int) $n[3], (int) $n[4]];
    $sensors[$i][2] = distance($sensors[$i][0], $sensors[$i][1], $beacons[$i][0], $beacons[$i][1]);
}

usort($sensors, function ($a, $b) {
    return ($a[0] - $a[2]) <=> ($b[0] - $b[2]);
});

$y = 2000000;
$slices = getSlices($y, $sensors);
usort($slices, function ($a, $b) {
    return $a[0] <=> $b[0];
});
$covered = 0;
$last = $slices[0][0];
for ($i = 0; $i < sizeof($slices); $i++) {
    if ($slices[$i][1] <= $last) continue;
    $l = ($slices[$i][0] <= $last) ? ($last + 1) : ($slices[$i][0] + 1);
    $covered += $slices[$i][1] - $l + 1;
    $last = $slices[$i][1];
}
printf('First star: %d%s', $covered, PHP_EOL);

$t0 = microtime(true);
$lim = 4000000;
$frequency = -1;
for($y = 0; $y < $lim; $y++) {
    if ($y % 150000 === 0) echo 'Row ' . $y . PHP_EOL;
    $slices = getSlices($y, $sensors, 0, $lim);
    usort($slices, function ($a, $b) {
        return $a[0] <=> $b[0];
    });
    $last = $slices[0][1];
    for ($i = 1; $i < sizeof($slices); $i++) {
        if ($last < $slices[$i][0]) {
            $frequency = ($last + 1) * $lim + $y;

            printf('Found %d, %d%s', ($last + 1), $y, PHP_EOL);
            break 2;
        }
        $last = ($slices[$i][1] > $last) ? $slices[$i][1] : $last;
    }
}
printf('Second star: %d (%f mcs)%s', $frequency, microtime(true) - $t0, PHP_EOL);

function getSlices($y, &$sensors, $xmin = -1, $xmax = -1): array
{
    $slices = [];

    foreach ($sensors as $sensor) {
        $dy = abs($sensor[1] - $y);
        if ($dy > $sensor[2]) continue;
        $dx = $sensor[2] - $dy;
        $s0 = $sensor[0] - $dx;
        if ($xmin !== -1 && $s0 < $xmin) $s0 = $xmin;
        $s1 = $sensor[0] + $dx;
        if ($xmax !== -1 && $s1 > $xmax) $s1 = $xmax;
        if ($s1 < $s0) continue;
        $slices[] = [$s0, $s1];
    }

    return $slices;
}

function distance(int $x1, int $y1, int $x2, int $y2): int
{
    return abs($x1 - $x2) + abs($y1 - $y2);
}