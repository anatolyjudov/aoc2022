<?php

declare(strict_types = 1);

$fp      = fopen('input.txt', 'r');
$regexp  = '/^\D+(\d)\D+([\d,\s]*)\s\s[^=]*=\s(.*)\s\s\D+(\d+)\D+(\d)\D+(\d)/';
$monkeys = [];
$monkey  = '';
while(($line = fgets($fp)) !== false) {

    if (trim($line) !== '') {
        $monkey .= trim($line) . '  ';
        continue;
    }

    if (!preg_match($regexp, $monkey, $monkey)) {
        die('unmatched: ' . $monkey);
    }

    $monkeys[$monkey[1]] = [
        'items' => array_map(function($v) { return (int) $v;}, explode(', ', $monkey[2])),
        'operation' => $monkey[3],
        'test' => (int) $monkey[4],
        'if' => [
            true => (int) $monkey[5],
            false => (int) $monkey[6]
        ],
        'inspections' => 0
    ];
    $monkey = '';
}

for($r = 0; $r < 20; $r++) {

    foreach(array_keys($monkeys) as $m) {
        $monkeys[$m]['inspections'] += sizeof($monkeys[$m]['items']);
        foreach($monkeys[$m]['items'] as $item) {
            [$a, $op, $b] = explode(' ', str_replace('old', (string) $item, $monkeys[$m]['operation']));
            switch ($op) {
                case '+': $worry = $a + $b; break;
                case '*': $worry = $a * $b; break;
                default : die('unknown operation: ' . $monkeys[$m]['operation']);
            }
            $worry = (int) floor($worry / 3);
            $catcher = $monkeys[$m]['if'][($worry % $monkeys[$m]['test'] === 0)];
            $monkeys[$catcher]['items'][] = $worry;
        }
        $monkeys[$m]['items'] = [];
    }
}

$max = [0, $monkeys[0]['inspections']];
foreach ($monkeys as $monkey) {
    if ($monkey['inspections'] > $max[0]) {
        $max[1] = $max[0];
        $max[0] = $monkey['inspections'];
        continue;
    }
    if ($monkey['inspections'] > $max[1]) {
        $max[1] = $monkey['inspections'];
    }
}

echo 'Monkey business (first star): ' . $max[0] * $max[1] . PHP_EOL;