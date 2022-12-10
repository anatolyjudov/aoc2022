<?php

declare(strict_types = 1);

$fp = fopen('input.txt', 'r');

$cycle = 1;
$x     = 1;

$resultsCycles = [20, 60, 100, 140, 180, 220];
$signal        = 0;

while($line = fgets($fp)) {
    $line = rtrim($line);

    if ($line === 'noop') {
        $loop = 1;
        $diffX = 0;
    } elseif (substr($line, 0, 4) === 'addx') {
        $loop = 2;
        $diffX = (int) substr($line, 5);
    } else {
        die('unknown line: ' . $line);
    }

    for($c = 0; $c < $loop; $c++) {

        $drawPos = ($cycle - 1) % 40;
        if ($drawPos >= ($x - 1) && $drawPos <= ($x + 1)) {
            echo '#';
        } else {
            echo '.';
        }
        if ($drawPos === 39) {
            echo "\r\n";
        }

        $cycle++;

        if (sizeof($resultsCycles) > 0 && $cycle > $resultsCycles[0]) {
            $signal += $resultsCycles[0] * $x;
            array_shift($resultsCycles);
        }
    }

    $x += $diffX;
}

echo 'First star: ' . $signal . PHP_EOL;