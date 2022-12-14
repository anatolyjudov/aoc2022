<?php

declare(strict_types = 1);

$rocks = explode(PHP_EOL, file_get_contents('input.txt'));
$hasFloor = true;

$lowestRock = 0;
foreach ($rocks as $rock) {
    $points = explode(' -> ', $rock);
    for ($i = 0; $i < (sizeof($points) - 1); $i++) {
        foreach(pointsBetween($points[$i], $points[$i+1]) as $point) {
            $cave[$point[1]][$point[0]] = 'r';
            if ($lowestRock < $point[1]) {
                $lowestRock = (int)$point[1];
            }
        }
    }
}

$caveFloor = $hasFloor ? $lowestRock + 2 : 0;
$sands = 0;
while(true) {
    $sands++;
    echo "Sand $sands is falling..." . PHP_EOL;
    $sand = [500, 0];
    $len = 0;
    while(true) {
        $len++;
        if (!$hasFloor && $sand[1] === $lowestRock) {
            echo 'path found at sand ' . $sands . PHP_EOL;
            break 2;
        }
        if (free($cave, $sand[0], $sand[1] + 1)) {
            $sand[1]++;
            echo '|';
            continue;
        }
        if (free($cave, $sand[0] - 1, $sand[1] + 1)) {
            $sand[1]++;
            $sand[0]--;
            echo '/';
            continue;
        }
        if (free($cave, $sand[0] + 1, $sand[1] + 1)) {
            $sand[1]++;
            $sand[0]++;
            echo '\\';
            continue;
        }
        $cave[$sand[1]][$sand[0]] = 's';
        echo ' and rest at ' . $sand[0] . ', ' . $sand[1] . ' after falling ' . $len . PHP_EOL;
        if ($len === 1) {
            echo ' sand is blocked on ' . $sands . PHP_EOL;
            break 2;
        }
        break;
    }
}

if ($hasFloor) {
    echo 'Second star: ' . $sands . PHP_EOL;
} else {
    echo 'First star: ' . ($sands - 1) . PHP_EOL;
}

function free(array &$cave, int $x, int $y): bool
{
    global $hasFloor, $caveFloor;
    if ($hasFloor && $y === $caveFloor) return false;
    return !isset($cave[$y][$x]);
}

function pointsBetween($p1, $p2)
{
    [$x1, $y1] = explode(',', $p1);
    [$x2, $y2] = explode(',', $p2);
    $dx = ($x2 > $x1) ? 1 : (($x2 < $x1) ? -1 : 0);
    $dy = ($y2 > $y1) ? 1 : (($y2 < $y1) ? -1 : 0);
    [$x, $y] = [$x1, $y1];
    do {
        yield [$x, $y];
        $x += $dx;
        $y += $dy;
    } while ($x !== ($x2 + $dx) || $y !== ($y2 + $dy));
}