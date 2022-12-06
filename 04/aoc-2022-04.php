<?php

$filename = 'input.txt';
$fp = fopen($filename, 'r');

$sum1 = 0;
$sum2 = 0;
while($instruction = fgets($fp)) {
    $sections = parseInstruction($instruction);
    if (hasOverlap($sections[0], $sections[1])) {
        $sum2 += 1;
        if (hasFullOverlap($sections[0], $sections[1])) {
            $sum1 += 1;
        }
    }
}

echo 'First star: ' . $sum1;
echo "\r\n";

echo 'Second star: ' . $sum2;
echo "\r\n";

/**
 * @param $instruction
 *
 * @return array [int, int], [int, int]
 */
function parseInstruction($instruction): array
{
    $sections = explode(',', trim($instruction));

    return [
        explode('-', $sections[0]),
        explode('-', $sections[1])
    ];
}

/**
 * @param array $s1
 * @param array $s2
 *
 * @return bool
 */
function hasFullOverlap(array $s1, array $s2): bool
{
    return (
        ($s1[0] >= $s2[0]) && ($s1[1] <= $s2[1])
        || ($s2[0] >= $s1[0]) && ($s2[1] <= $s1[1])
    );
}

/**
 * @param array $s1
 * @param array $s2
 *
 * @return bool
 */
function hasOverlap(array $s1, array $s2): bool
{
    return !(
    ($s1[0] < $s2[0] && $s1[1] < $s2[0])
    || ($s2[0] < $s1[0] && $s2[1] < $s1[0])
    );
}