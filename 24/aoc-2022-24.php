<?php

declare(strict_types = 1);

ini_set('memory_limit', '2048M');

const FREE  = 0;
const LEFT  = 1;
const RIGHT = 2;
const UP    = 4;
const DOWN  = 8;

$data = explode(PHP_EOL, file_get_contents('input.txt'));

$y = 0;
foreach ($data as $str) {
    if ((substr($str, 0, 3) === '#.#')
        || (substr($str, 0, 3) === '###')) {
        continue;
    }
    $row = trim($str, "\r\n#");
    $xCycle = strlen($row);
    for($x = 0; $x < strlen($row); $x++) {
        switch ($row[$x]) {
            case '.':
                break;
            case '>':
                $blizzards[RIGHT][$y][$x] = 1;
                break;
            case '<':
                $blizzards[LEFT][$y][$x] = 1;
                break;
            case 'v':
                $blizzards[DOWN][$x][$y] = 1;
                break;
            case '^':
                $blizzards[UP][$x][$y] = 1;
                break;
        }
    }
    $y++;
}
$yCycle = $y;
$timeCycle = $xCycle * $yCycle;
$callCount = 0;
$start = [0, -1];
$finish = [$xCycle - 1, $yCycle];

{
    $fastestTimes  = [];
    $fastestFinish = -1;
    getFastest(0, $start, $finish, $fastestTimes);
    $result = findResult($fastestTimes, $finish);
    $total  = $result;
    printf('First star: %d%s', $result, PHP_EOL);

    $fastestTimes  = [];
    $fastestFinish = -1;
    $fromMinute    = $total % $timeCycle;
    getFastest($fromMinute, $finish, $start, $fastestTimes);
    $result = findResult($fastestTimes, $start);
    $total += $result - $fromMinute;
    printf('Way back: %d%s', $result - $fromMinute, PHP_EOL);

    $fastestTimes  = [];
    $fastestFinish = -1;
    $fromMinute    = $total % $timeCycle;
    getFastest($fromMinute, $start, $finish, $fastestTimes);
    $result = findResult($fastestTimes, $finish);
    $total += $result - $fromMinute;
    printf('Go again: %d%s', $result - $fromMinute, PHP_EOL);

    printf('Second star: %d%s', $total, PHP_EOL);
}

function findResult(array &$fastestTimes, array $pos)
{
    $result = -1;
    foreach (array_keys($fastestTimes) as $m) {
        if (empty($fastestTimes[$m][$pos[0]][$pos[1]])) continue;
        $h = $fastestTimes[$m][$pos[0]][$pos[1]];
        if (($result === -1) || ($h < $result)) {
            $result = $h;
        }
    }
    return $result;
}

function getFastest(int $minute, array $current, array $finish, array &$fastestTimes): ?int
{
    global $timeCycle;
    global $callCount;
    global $fastestFinish;

    // global counter
    $callCount++;
    if ($callCount % 100000 === 0) {
        p($callCount);
    }

    // if it's border, return null
    if (border($current[0], $current[1])) {
        return null;
    }

    // if it's a blizzard, return null
    if (get($minute, $current[0], $current[1]) !== 0) {
        return null;
    }

    // if we could have been here faster
    if (!empty($fastestTimes[$minute % $timeCycle][$current[0]][$current[1]])) {
        if ($minute >= $fastestTimes[$minute % $timeCycle][$current[0]][$current[1]]) {
            return null;
        }
    }
    $fastestTimes[$minute % $timeCycle][$current[0]][$current[1]] = $minute;

    // if it's a finish
    if (($current[0] === $finish[0]) && ($current[1] === $finish[1])) {
        if ($fastestFinish === -1 || $minute < $fastestFinish) {
            $fastestFinish = $minute;
        }
        return 0;
    }

    // if it's already longer than fastest finish
    if (($fastestFinish !== -1) && ($minute >= $fastestFinish)) {
        return null;
    }

    $fastestTry = null;
    if ($current[0] <= $finish[0]) {
        if ($current[1] <= $finish[1]) {
            $opts = [[1, 0], [0, 1], [0, 0], [-1, 0], [0, -1]];
        } else {
            $opts = [[1, 0], [0, -1], [0, 0], [-1, 0], [0, 1]];
        }
    } else {
        if ($current[1] <= $finish[1]) {
            $opts = [[-1, 0], [0, 1], [0, 0], [0, -1], [1, 0]];
        } else {
            $opts = [[-1, 0], [0, -1], [0, 0], [0, 1], [1, 0]];
        }
    }
    foreach ($opts as $opt) {
        $next = [$current[0] + $opt[0], $current[1] + $opt[1]];
        $try = getFastest($minute + 1, $next, $finish, $fastestTimes);
        if (empty($try)) continue;
        if (empty($fastestTry) || $try < $fastestTry) {
            $fastestTry = $try;
        }
    }

    return $fastestTry;
}

function border($x, $y): bool
{
    global $xCycle, $yCycle;

    $border = false;

    if ($x < 0 || $y < 0 || $x === $xCycle || $y === $yCycle) {
        if (($x !== 0 || $y !== -1) && ($x !== ($xCycle - 1) || $y !== $yCycle)) {
            $border = true;
        }
    }

    return $border;
}

function get($t, $x, $y): int
{
    global $blizzards, $xCycle, $yCycle;

    $res = FREE;

    if (!empty($blizzards[LEFT][$y][($x + $t) % $xCycle])) {
        $res += LEFT;
    }

    if (!empty($blizzards[RIGHT][$y][$xCycle - ($xCycle - $x - 1 + $t) % $xCycle - 1])) {
        $res += RIGHT;
    }

    if (!empty($blizzards[UP][$x][($y + $t) % $yCycle])) {
        $res += UP;
    }

    if (!empty($blizzards[DOWN][$x][$yCycle - ($yCycle - $y - 1 + $t) % $yCycle - 1])) {
        $res += DOWN;
    }

    return $res;
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