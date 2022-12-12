<?php

declare(strict_types = 1);

$fileData = explode(PHP_EOL, trim(file_get_contents('input.txt')));

$map = [];
$start = $end = [0, 0];
foreach($fileData as $row) {
    $line = [];

    for($i = 0; $i < strlen($row); $i++) {
        if ($row[$i] === 'S') {
            $s = 'a';
            $start = [sizeof($map), $i];
        } elseif ($row[$i] === 'E') {
            $s = 'z';
            $end = [sizeof($map), $i];
        } else {
            $s = $row[$i];
        }
        $line[] = ord($s) - ord('a');
    }

    $map[] = $line;
}

$e = array_fill(0, sizeof($map), array_fill(0, sizeof($map[0]), -1));

function stepFrom(int $y, int $x, int $step, array &$e, array &$map, int &$min0)
{
    $e[$y][$x] = $step;

    if ($map[$y][$x] === 0 && ($min0 === -1 || $step < $min0)) {
        $min0 = $step;
    }

    foreach([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dy, $dx]) {
        $xt = $x + $dx;
        $yt = $y + $dy;
        if (($xt < 0) || ($yt < 0) || ($yt >= sizeof($map)) || ($xt >= sizeof($map[0]))) continue;
        if ($map[$y][$x] - $map[$yt][$xt] > 1) continue;
        if ($e[$yt][$xt] <= ($step + 1) && ($e[$yt][$xt] !== -1)) continue;

        stepFrom($yt, $xt, $step + 1, $e, $map, $min0);
    }
}

$min0 = -1;
stepFrom($end[0], $end[1], 0, $e, $map, $min0);

printf('First star: %d' . PHP_EOL, $e[$start[0]][$start[1]]);
printf('Second star: %d' . PHP_EOL, $min0);