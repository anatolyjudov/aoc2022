<?php

const MONKEY_NUMBER   = 'number';
const MONKEY_FUNCTION = 'function';
const MONKEY_EQUAL    = 'equal';
const ROOT_NAME       = 'root';
const HUMAN_NAME      = 'humn';

$functions = [
    '+' => function($a, $b) {
            //p($a, '+', $b, '=', $a + $b);
            return $a + $b;
        },
    '-' => function($a, $b) {
            //p($a, '-', $b, '=', $a - $b);
            return $a - $b;
        },
    '*' => function($a, $b) {
            //p($a, '*', $b, '=', $a * $b);
            return $a * $b;
        },
    '/' => function($a, $b) {
            //p($a, '/', $b, '=', $a / $b);
            return $a / $b;
        },
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

// prepare for the second task
$monkeys2 = $monkeys;
unset($monkeys2[HUMAN_NAME]);
unset($monkeys2[ROOT_NAME]);

// split root
{
    $mainBranch = '';
    $otherBranchValue = 0;

    foreach (['a', 'b'] as $ab) {
        $value = getMonkeyValue($monkeys[ROOT_NAME][$ab], $monkeys2);
        if ($value === false) {
            $mainBranch = $monkeys[ROOT_NAME][$ab];
        } else {
            $otherBranchValue = $value;
        }
    }
}

$monkeys3 = [];
resolveMonkey(HUMAN_NAME, $monkeys2, $monkeys3);

printf('Second star: %d%s', getMonkeyValue(HUMAN_NAME, $monkeys3), PHP_EOL);

function recursiveCopy($name, &$monkeys, &$monkeysRes)
{
    if (!isset($monkeys[$name])) {
        p('Need to resolve ' . $name);
        resolveMonkey($name, $monkeys, $monkeysRes);
        return;
    }
    $monkeysRes[$name] = $monkeys[$name];
    if ($monkeysRes[$name]['type'] === MONKEY_FUNCTION) {
        recursiveCopy($monkeysRes[$name]['a'], $monkeys, $monkeysRes);
        recursiveCopy($monkeysRes[$name]['b'], $monkeys, $monkeysRes);
    }
}

function resolveMonkey($lookForName, &$monkeys, &$monkeysRes)
{
    global $mainBranch, $otherBranchValue;
    if ($lookForName === $mainBranch) {
        $monkeysRes[$mainBranch] = [
            'type'  => MONKEY_NUMBER,
            'value' => $otherBranchValue
        ];
        return;
    }

    if (isset($monkeys[$lookForName]) && $monkeys[$lookForName]['type'] === MONKEY_NUMBER) {
        $monkeysRes[$lookForName] = $monkeys[$lookForName];
        return;
    }

    $deps = findDependentMonkey($lookForName, $monkeys);
    if (sizeof($deps) > 1) {
        var_dump($deps);
        die('Found more than one dependency for ' . $lookForName);
    }
    if (sizeof($deps) === 0) {
        if (isset($monkeys[$lookForName]) && ($monkeys[$lookForName]['type'] === MONKEY_FUNCTION)) {
            recursiveCopy($lookForName, $monkeys, $monkeysRes);
            return;
        }
        die('Error trying to resolve ' . $lookForName);
    }
    $dep = $deps[0];

    // create reverted statement
    $m = $monkeys[$dep];
    unset($monkeys[$dep]);
    $m2 = ['type' => MONKEY_FUNCTION];
    if ($m['op'] === '+') {
        $m2['a'] = $dep;
        $m2['op'] = '-';
        if ($m['a'] === $lookForName) {
            // dep = lfn + smth
            $m2['b'] = $m['b'];
        } else {
            // dep = smth + lfn
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
        if ($m['a'] === $lookForName) {
            // dep = lfn / smth
            $m2['a'] = $dep;
            $m2['b'] = $m['b'];
            $m2['op'] = '*';
        } else {
            // dep = smth / lfn
            $m2['a'] = $m['a'];
            $m2['b'] = $dep;
            $m2['op'] = '/';
        }
    }
    if ($m['op'] === '-') {
        if ($m['a'] === $lookForName) {
            // dep = lfn - smth
            $m2['a'] = $dep;
            $m2['b'] = $m['b'];
            $m2['op'] = '+';
        } else {
            // dep = smth - lfn
            $m2['a'] = $m['a'];
            $m2['b'] = $dep;
            $m2['op'] = '-';
        }
    }

    // save reverted to the new monkey array
    $monkeysRes[$lookForName] = $m2;
    resolveMonkey($m2['a'], $monkeys, $monkeysRes);
    resolveMonkey($m2['b'], $monkeys, $monkeysRes);
}

function findDependentMonkey(string $lookForName, &$monkeys): array
{
    $dependent = [];
    foreach($monkeys as $name => $monkey) {
        if ($monkey['type'] !== MONKEY_FUNCTION) continue;
        if (($monkey['a'] === $lookForName) || ($monkey['b'] === $lookForName)) {
            $dependent[] = $name;
        }
    }
    return $dependent;
}

function getMonkeyValue(string $name, array &$monkeys)
{
    global $functions;

    if (empty($monkeys[$name])) {
        //p('Don\'t know this monkey: ' . $name);
        return false;
    }

    if ($monkeys[$name]['type'] === MONKEY_NUMBER) {
        $result = $monkeys[$name]['value'];
        return $result;
    }

    if ($monkeys[$name]['type'] === MONKEY_FUNCTION) {
        $a = getMonkeyValue($monkeys[$name]['a'], $monkeys);
        $b = getMonkeyValue($monkeys[$name]['b'], $monkeys);
        if ($a === false || $b === false) return false;
        $result = $functions[$monkeys[$name]['op']]($a, $b);
        return $result;
    }

    if ($monkeys[$name]['type'] === MONKEY_EQUAL) {
        $result = getMonkeyValue($monkeys[$name]['value'], $monkeys);
        return $result;
    }

    die('Dont know the type of this monkey ' . $monkeys[$name]['type']);
}

function printMonkeys(array &$monkeys)
{
    foreach ($monkeys as $name => $monkey) {
        echo $name . ': ';
        if ($monkey['type'] === MONKEY_NUMBER || $monkey['type'] === MONKEY_EQUAL) {
            echo $monkey['value'];
        }
        if ($monkey['type'] === MONKEY_FUNCTION) {
            echo $monkey['a'] . ' ' . $monkey['op'] . ' ' . $monkey['b'];
        }
        echo PHP_EOL;
    }
}

function p(...$args)
{
    foreach ($args as $arg) {
        if (is_array($arg) || is_object($arg)) {
            print_r($arg);
            continue;
        }
        echo $arg . ' ';
    }
    echo PHP_EOL;
}