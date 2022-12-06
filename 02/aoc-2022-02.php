<?php

$inputLines = file('input.txt', FILE_IGNORE_NEW_LINES);

$scoreTable1 = [
    'A' => [
        'X' => 1 + 3,
        'Y' => 2 + 6,
        'Z' => 3 + 0
    ],
    'B' => [
        'X' => 1 + 0,
        'Y' => 2 + 3,
        'Z' => 3 + 6
    ],
    'C' => [
        'X' => 1 + 6,
        'Y' => 2 + 0,
        'Z' => 3 + 3
    ]
    ];

$scoreTable2 = [
    'A' => [
        'X' => 3 + 0,
        'Y' => 1 + 3,
        'Z' => 2 + 6
    ],
    'B' => [
        'X' => 1 + 0,
        'Y' => 2 + 3,
        'Z' => 3 + 6
    ],
    'C' => [
        'X' => 2 + 0,
        'Y' => 3 + 3,
        'Z' => 1 + 6
    ]
    ];

$score1 = 0;
$score2 = 0;

foreach($inputLines as $roundData) {
    [$opp, $you] = explode(" ", $roundData);

    $score1 += $scoreTable1[$opp][$you];
    $score2 += $scoreTable2[$opp][$you];
}

echo $score1 . "\r\n" . $score2;