<?php

declare(strict_types = 1);

$rows = explode(PHP_EOL, file_get_contents('input.txt'));

$pairs = $pair = $all = [];
foreach($rows as $row) {
    if ($row === '') {
        $pairs[] = $pair;
        $pair = [];
        continue;
    }
    $pair[] = $parsed = json_decode($row);
    $all[]  = $parsed;
}

$correctSum = $pos2 = $pos6 = 0;
foreach($pairs as $index => $pair)
    if (compare($pair[0], $pair[1]) < 0)
        $correctSum += $index + 1;

array_push($all, [[2]], [[6]]);
usort($all, 'compare');

for($i = 0; $i < sizeof($all); $i++) {
    if ($all[$i] === [[2]]) $pos2 = $i + 1;
    if ($all[$i] === [[6]]) $pos6 = $i + 1;
}

printf('First star: %d, second star: %d', $correctSum, $pos2 * $pos6);

function compare($left, $right): int
{
    if (is_int($left) && is_int($right))
        return $left <=> $right;

    if (!is_array($left))  $left = [$left];
    if (!is_array($right)) $right = [$right];

    for($i = 0; $i < sizeof($left); $i++) {
        if (!array_key_exists($i, $right)) return 1;
        if (($compare = compare($left[$i], $right[$i])) !== 0) return $compare;
    }

    if (sizeof($left) < sizeof($right)) return -1;

    return 0;
}