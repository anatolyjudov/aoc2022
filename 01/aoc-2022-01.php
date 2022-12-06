<?php

$inputLines = file('input.txt', FILE_IGNORE_NEW_LINES);

$elves = [];

$currentElf = 0;

foreach($inputLines as $line) {
    if ($line === '') {
        $currentElf++;
        continue;
    }

    $elves[$currentElf]['foods'][] = (int)$line;
    $elves[$currentElf]['total'] += (int)$line;
}

var_dump($elves);

$maxValues = [0, 0, 0];
foreach($elves as $elf) {
    if ($elf['total'] > $maxValues[0]) {
        $maxValues[2] = $maxValues[1];
        $maxValues[1] = $maxValues[0];
        $maxValues[0] = $elf['total'];
        continue;
    }
    if ($elf['total'] > $maxValues[1]) {
        $maxValues[2] = $maxValues[1];
        $maxValues[1] = $elf['total'];
        continue;
    }
    if ($elf['total'] > $maxValues[2]) {
        $maxValues[2] = $elf['total'];
        continue;
    }
}

var_dump($maxValues);

var_dump(array_sum($maxValues));