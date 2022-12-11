<?php

declare(strict_types = 1);

$fp = fopen('input.txt', 'r');

class Item
{
    protected static array $divisions = [];

    protected int $initial;

    protected array $remainders = [];

    public function __construct(int $worry)
    {
        $this->initial = $worry;
    }

    public static function addDivision(int $divisionBy)
    {
        if (!in_array($divisionBy, self::$divisions)) {
            self::$divisions[] = $divisionBy;
        }
    }

    public function test(int $div): bool
    {
        return $this->getRemainder($div) === 0;
    }

    public function add(int $b)
    {
        foreach(self::$divisions as $div) {
            $this->remainders[$div] = ($this->getRemainder($div) + $b) % $div;
        }
    }

    public function multiply(int $b)
    {
        foreach(self::$divisions as $div) {
            $this->remainders[$div] = ($this->getRemainder($div) * $b) % $div;
        }
    }

    public function square()
    {
        foreach(self::$divisions as $div) {
            $this->remainders[$div] = ($this->getRemainder($div) ** 2) % $div;
        }
    }

    protected function getRemainder(int $division): int
    {
        return $this->remainders[$division] ?? $this->initial;
    }
}

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
        'items' => array_map(function($v) { return new Item((int) $v); }, explode(', ', $monkey[2])),
        'operation' => $monkey[3],
        'test' => (int) $monkey[4],
        'if' => [
            true => (int) $monkey[5],
            false => (int) $monkey[6]
        ],
        'inspections' => 0
    ];
    Item::addDivision((int) $monkey[4]);
    $monkey = '';
}

for($r = 0; $r < 10000; $r++) {

    foreach(array_keys($monkeys) as $m) {
        $monkeys[$m]['inspections'] += sizeof($monkeys[$m]['items']);
        foreach($monkeys[$m]['items'] as $item) {
            if ($monkeys[$m]['operation'] === 'old * old') {
                $item->square();
            } else {
                switch ($monkeys[$m]['operation'][4]) {
                    case '+':
                        $item->add((int) substr($monkeys[$m]['operation'], 6)); break;
                    case '*':
                        $item->multiply((int) substr($monkeys[$m]['operation'], 6)); break;
                    default:
                        die('unknown operation: ' . $monkeys[$m]['operation']);
                }
            }
            $catcher = $monkeys[$m]['if'][$item->test($monkeys[$m]['test'])];
            $monkeys[$catcher]['items'][] = $item;
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

echo 'Monkey business (second star): ' . $max[0] * $max[1] . PHP_EOL;