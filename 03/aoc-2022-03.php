<?php

$inputLines = file('input.txt', FILE_IGNORE_NEW_LINES);

$sum1 = 0;
foreach($inputLines as $rucksack) {
    $sum1 += getValue(getRucksackCommonItem($rucksack));
}

echo 'First star: ' . $sum1 . "\r\n";

$sum2 = 0;
for($i = 0; $i < sizeof($inputLines); $i += 3) {
    $sum2 += getValue(
        getThreeRucksacksCommonItem($inputLines[$i], $inputLines[$i + 1], $inputLines[$i + 2])
    );
}

echo 'Second star: ' . $sum2 . "\r\n";

// functions

function getValue($item)
{
    $ord = ord($item);
    if ($ord < 91) {
        return $ord - 64 + 26;
    }
    return $ord - 96;
}

function getRucksackCommonItem($rucksack)
{
    $capacity = strlen($rucksack);
    $compartmentSize = $capacity >> 1;

    for($i = 0; $i < $compartmentSize; $i++) {
        $item = $rucksack[$i];
        for($o = $compartmentSize; $o < $capacity; $o++) {
            if ($item === $rucksack[$o]) {
                return $item;
            }
        }
    }

    die('Common item not found: ' . $rucksack . "\r\n");
}

function getThreeRucksacksCommonItem($r1, $r2, $r3)
{
    for($i = 0; $i < strlen($r1); $i++) {
        $item = $r1[$i];
        for($o = 0; $o < strlen($r2); $o++) {
            if ($item === $r2[$o]) {
                for($k = 0; $k < strlen($r3); $k++) {
                    if ($item === $r3[$k]) {
                        return $item;
                    }
                }
            }
        }
    }

    die('Common item not found in: ' . "\r\n" . $r1 . "\r\n" . $r2 . "\r\n" . $r3 . "\r\n");
}