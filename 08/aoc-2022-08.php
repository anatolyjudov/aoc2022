<?php

$filename = 'input.txt';
$fp = fopen($filename, 'r');

$forest = [];
while (!feof($fp) && !empty($line = fgets($fp))) {
    $forest[] = str_split(trim($line));
}

$size       = sizeof($forest[0]);
$visibility = array_fill(0, $size, array_fill(0, $size, 0));
$scenic     = array_fill(0, $size, array_fill(0, $size, [0, 0, 0, 0]));

// 'Look' at the forest from four different directions:
// from the left side, from the top, from the right and from the bottom
for ($c = 1; $c <= 4; $c++) {

    for ($a = 0; $a < $size; $a++) {
        $max   = 0;
        $trees = [];

        for ($b = 0; $b < $size; $b++) {

            [$y, $x] = transform($a, $b, $c);

            if (
                ($x === 0) || ($y === 0) || ($x === $size - 1) || ($y === $size - 1)
                || ($forest[$y][$x] > $max)
            ) {
                $visibility[$y][$x] |= 2 ** $c;
                $max = $forest[$y][$x];
            }

            $scenic[$y][$x][$c - 1] = 0;

            for ($s = 0; $s < sizeof($trees); $s++) {
                $scenic[$y][$x][$c - 1]++;
                if ($trees[sizeof($trees) - $s - 1] >= $forest[$y][$x]) {
                    break;
                }
            }

            $trees[] = $forest[$y][$x];
        }
    }
}

$solution1 = 0;
$solution2 = 0;
for ($a = 0; $a < $size; $a++) {
    for ($b = 0; $b < $size; $b++) {
        if ($visibility[$a][$b] > 0) {
            $solution1++;
        }
        $s = $scenic[$a][$b][0] * $scenic[$a][$b][1] * $scenic[$a][$b][2] * $scenic[$a][$b][3];
        if ($solution2 < $s) {
            $solution2 = $s;
        }
    }
}

echo $solution1 . "\r\n";
echo $solution2 . "\r\n";

/**
 * Transform a and b indexes to the current direction's coordinates
 *
 * @param int $a
 * @param int $b
 * @param int $c
 * @return array|int[]
 */
function transform($a, $b, $c): array
{
    global $size;

    switch ($c) {
        case 1: return [$a, $b];
        case 2: return [$b, $size - $a - 1];
        case 3: return [$size - $a - 1, $size - $b - 1];
        case 4: return [$size - $b - 1, $a];
        default:
            die ('unknown corner given');
    }
}
