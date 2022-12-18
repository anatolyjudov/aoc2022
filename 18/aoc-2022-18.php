<?php

declare(strict_types = 1);

//ini_set('memory_limit', '2048M');
//$mem = ini_get('memory_limit');

$fp = file_get_contents('input.txt');

$units = [];

$mins = [];
$maxs = [];
foreach(explode(PHP_EOL, $fp) as $c) {
    [$x, $y, $z] = explode(',', trim($c));
    $units[$x][$y][$z] = 1;
    if (empty($maxs)) {
        $mins = [$x, $y, $z];
        $maxs = [$x, $y, $z];
    }
    if ($mins[0] > $x) $mins[0] = $x;
    if ($mins[1] > $y) $mins[1] = $y;
    if ($mins[2] > $z) $mins[2] = $z;
    if ($maxs[0] < $x) $maxs[0] = $x;
    if ($maxs[1] < $y) $maxs[1] = $y;
    if ($maxs[2] < $z) $maxs[2] = $z;
}

$trys = [
    [1, 0, 0], [-1, 0, 0],
    [0, 1, 0], [0, -1, 0],
    [0, 0, 1], [0, 0, -1]
];


$count = 0;
foreach($units as $x => $colYZ) {
    foreach ($colYZ as $y => $colZ) {
        foreach ($colZ as $z => $foo) {
            // try cube
            foreach ($trys as $tr) {
                if (empty($units[$x + $tr[0]][$y + $tr[1]][$z + $tr[2]])) {
                    $count++;
                }
            }
        }

    }
}
$paths = [];

findPaths($mins[0], $mins[1], $mins[2], 1);

function findPaths($x, $y, $z, $len)
{
    global $trys, $paths;

    $paths[$x][$y][$z] = $len;

    if ($len === 100) return;
    foreach ($trys as $tr) {
        $newX = $x + $tr[0];
        $newY = $y + $tr[1];
        $newZ = $z + $tr[2];
        if (!check($newX, $newY, $newZ)) continue;
        if (!empty($paths[$newX][$newY][$newZ]) && ($paths[$newX][$newY][$newZ] <= $len + 1)) continue;
        findPaths($newX, $newY, $newZ, $len + 1);
    }
}

function check($x, $y, $z): bool
{
    global $mins, $maxs, $units;

    if (
        $x < ($mins[0] - 1) || $x > ($maxs[0] + 1)
        || $y < ($mins[1] - 1) || $y > ($maxs[1] + 1)
        || $z < ($mins[2] - 1) || $z > ($maxs[2] + 1)
        || !empty($units[$x][$y][$z])
        ) {
        return false;
    }
    return true;
}

$countOuter = 0;
foreach($units as $x => $colYZ) {
    foreach ($colYZ as $y => $colZ) {
        foreach ($colZ as $z => $foo) {
            // try cube
            foreach ($trys as $tr) {
                if (!empty($paths[$x + $tr[0]][$y + $tr[1]][$z + $tr[2]])) {
                    $countOuter++;
                }
            }
        }

    }
}

// Print
for($x = $mins[0]; $x <= $maxs[0]; $x++) {
    echo $x . ':' . PHP_EOL;
    for($y = $mins[1]; $y <= $maxs[1]; $y++) {
        for($z = $mins[2]; $z <= $maxs[2]; $z++) {
            if (!empty($units[$x][$y][$z])) {
                echo 'O';
            } else {
                if (empty($paths[$x][$y][$z])) {
                    echo '.';
                } else {
                    echo ' ';
                }
            }
        }
        echo PHP_EOL;
    }
    echo '--------' . PHP_EOL . PHP_EOL;
}

printf('First star %d%s', $count, PHP_EOL);
printf('Second star %d%s', $countOuter, PHP_EOL);