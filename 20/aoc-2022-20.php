<?php

declare(strict_types = 1);

$filename      = 'input.txt';
$decryptionKey = 811589153;
$mixes         = 10;

$numbers = [];
$i       = 0;
foreach (explode(PHP_EOL, file_get_contents($filename)) as $line) {
    if (trim($line) === '') continue;
    $numbers[$i] = [
        'v' => ((int) trim($line)) * $decryptionKey,
        'p' => $i
    ];
    $i++;
}

for ($m = 0; $m < $mixes; $m++) {
    for ($i = 0; $i < sizeof($numbers); $i++) {
        $cp = getFromOriginalPosition($i, $numbers);
        if ($numbers[$cp]['v'] === 0) continue;
        $np = ($cp + $numbers[$cp]['v']) % (sizeof($numbers) - 1);
        if ($np <= 0) $np = sizeof($numbers) - 1 + $np;
        if ($cp === $np) continue;
        $v = $numbers[$cp];
        array_splice($numbers, $cp, 1);
        array_splice($numbers, $np, 0, [$v]);
    }
}

for($i = 0; $i < sizeof($numbers); $i++)
    if ($numbers[$i]['v'] === 0)
        $zeroPos = $i;

$a1 = $numbers[($zeroPos + 1000) % sizeof($numbers)]['v'];
$a2 = $numbers[($zeroPos + 2000) % sizeof($numbers)]['v'];
$a3 = $numbers[($zeroPos + 3000) % sizeof($numbers)]['v'];
printf('%d + %d + %d = %d%s', $a1, $a2, $a3, $a1 + $a2 + $a3, PHP_EOL);

function getFromOriginalPosition($p, &$numbers): int
{
    for($i = 0; $i < sizeof($numbers); $i++) {
        if ($numbers[$i]['p'] === $p) return $i;
    }
    die();
}