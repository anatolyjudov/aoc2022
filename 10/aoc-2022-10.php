<?php

declare(strict_types = 1);

$fp = fopen('input.txt', 'r');

$resultsCycles = range(20, 220, 40);
$signal        = 0;

$cycle = $x = 1;

while($line = fgets($fp)) {
    $line = rtrim($line);

    $diffX = 0;
    if ($line === 'noop') {
        $loop = 1;
    } elseif (substr($line, 0, 4) === 'addx') {
        $loop = 2;
        $diffX = (int) substr($line, 5);
    }

    for($c = 0; $c < $loop; $c++) {

        $drawPos = ($cycle - 1) % 40;
        echo ($drawPos >= ($x - 1) && $drawPos <= ($x + 1)) ? '#' : '.';
        if ($drawPos === 39) echo "\r\n";

        $cycle++;

        if (sizeof($resultsCycles) > 0 && $cycle > $resultsCycles[0]) {
            $signal += $resultsCycles[0] * $x;
            array_shift($resultsCycles);
        }
    }

    $x += $diffX;
}

echo 'First star: ' . $signal . PHP_EOL;