<?php

const MONKEY_NUMBER   = 'number';
const MONKEY_FUNCTION = 'function';
const MONKEY_EQUAL    = 'equal';
const ROOT_NAME       = 'root';
const HUMAN_NAME      = 'humn';

$functions = [
    '+' => function($a, $b) { return $a + $b; },
    '-' => function($a, $b) { return $a - $b; },
    '*' => function($a, $b) { return $a * $b; },
    '/' => function($a, $b) { return $a / $b; },
];

$monkeys = [];
foreach (explode(PHP_EOL, file_get_contents('input.txt')) as $item) {
    if (($item = trim($item)) === '') continue;

    $monkey   = substr($item, 0, 4);
    $valueStr = trim(substr($item, 6));
    if (is_numeric($valueStr)) {
        $monkeys[$monkey] = [
            'type'  => MONKEY_NUMBER,
            'value' => $valueStr
        ];
        continue;
    }
    if (strlen($valueStr) === 4) {
        $monkeys[$monkey] = [
            'type'  => MONKEY_EQUAL,
            'value' => $valueStr
        ];
        continue;
    }
    [$a, $op, $b] = explode(' ', $valueStr);
    $monkeys[$monkey] = [
        'type' => MONKEY_FUNCTION,
        'a'  => $a,
        'b'  => $b,
        'op' => $op
    ];
}

printf('First star: %d%s', getMonkeyValue(ROOT_NAME, $monkeys), PHP_EOL);

function resolveMonkey($lookForName, &$monkeys, &$monkeys2)
{
    if (isset($monkeys2[$lookForName])) return;

    $deps = findDependentMonkey($lookForName, $monkeys);
    if (sizeof($deps) > 1) {
        var_dump($deps);
        die('Found more than one dependency for ' . $lookForName);
    }
    if (sizeof($deps) === 0) {
        if (empty($monkeys[$lookForName])) {
            die('Couldnt resolve ' . $lookForName . ' not found not as dependency not as formula');
        }
        $monkeys2[$lookForName] = $monkeys[$lookForName];
        unset($monkeys[$lookForName]);
        resolveMonkey($monkeys2[$lookForName]['a'], $monkeys, $monkeys2);
        resolveMonkey($monkeys2[$lookForName]['b'], $monkeys, $monkeys2);
        return;
    }
    $dep = $deps[0];

    $m = $monkeys[$dep];
    unset($monkeys[$dep]);
    $m2 = ['type' => MONKEY_FUNCTION];
    if ($m['op'] === '+') {
        $m2['a'] = $dep;
        $m2['op'] = '-';
        if ($m['a'] === $lookForName) {
            $m2['b'] = $m['b'];
        } else {
            $m2['b'] = $m['a'];
        }
    }
    if ($m['op'] === '*') {
        $m2['a'] = $dep;
        $m2['op'] = '/';
        if ($m['a'] === $lookForName) {
            $m2['b'] = $m['b'];
        } else {
            $m2['b'] = $m['a'];
        }
    }
    if ($m['op'] === '/') {
        $m2['a'] = $dep;
        if ($m['a'] === $lookForName) {
            $m2['b'] = $m['b'];
            $m2['op'] = '*';
        } else {
            $m2['b'] = $m['a'];
            $m2['op'] = '/';
        }
    }
    if ($m['op'] === '-') {
        $m2['a'] = $dep;
        if ($m['a'] === $lookForName) {
            $m2['b'] = $m['b'];
            $m2['op'] = '+';
        } else {
            $m2['b'] = $m['a'];
            $m2['op'] = '-';
        }
    }
    $monkeys2[$lookForName] = $m2;
    resolveMonkey($m2['a'], $monkeys, $monkeys2);
    resolveMonkey($m2['b'], $monkeys, $monkeys2);
}

function findDependentMonkey(string $lookForName, &$monkeys): array
{
    $dependent = [];
    foreach($monkeys as $name => $monkey) {
        if ($monkey['type'] !== MONKEY_FUNCTION) continue;
        if ($monkey['a'] === $lookForName || $monkey['b'] === $lookForName) {
            if ($name === ROOT_NAME) {
                die('Found dependency on root');
            }
            $dependent[] = $name;
        }
    }
    return $dependent;
}

function getMonkeyValue(string $name, array &$monkeys)
{
    global $recCount;
    global $functions;

    $recCount[$name] = ($recCount[$name] ?? 0) + 1;
    if ($recCount[$name] > 2) {
        echo 'Probably dead loop here ' . $name . PHP_EOL;
        var_dump($recCount);
        die();
    }

    if (empty($monkeys[$name])) die('Dont know this monkey: ' . $name);

    if ($monkeys[$name]['type'] === MONKEY_NUMBER) {
        return $monkeys[$name]['value'];
    }

    if ($monkeys[$name]['type'] === MONKEY_FUNCTION) {
        $a = getMonkeyValue($monkeys[$name]['a'], $monkeys);
        $b = getMonkeyValue($monkeys[$name]['b'], $monkeys);
        return $functions[$monkeys[$name]['op']]($a, $b);
    }

    if ($monkeys[$name]['type'] === MONKEY_EQUAL) {
        return getMonkeyValue($monkeys[$name]['value'], $monkeys);
    }

    die('Dont know the type of this monkey ' . $monkeys[$name]['type']);
}

function printMonkeys(array &$monkeys)
{
    foreach ($monkeys as $name => $monkey) {
        echo $name . ': ';
        if ($monkey['type'] === MONKEY_NUMBER || $monkey['type'] === MONKEY_EQUAL) {
            echo $monkey['value']->getString();
        }
        if ($monkey['type'] === MONKEY_FUNCTION) {
            echo $monkey['a'] . ' ' . $monkey['op'] . ' ' . $monkey['b'];
        }
        echo PHP_EOL;
    }
}