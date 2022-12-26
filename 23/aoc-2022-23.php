<?php

declare(strict_types = 1);

const N  = [ 0, -1];
const NE = [ 1, -1];
const E  = [ 1,  0];
const SE = [ 1,  1];
const S  = [ 0,  1];
const SW = [-1,  1];
const W  = [-1,  0];
const NW = [-1, -1];

$elves = [];
$y = 0;
foreach(explode(PHP_EOL, file_get_contents('input.txt')) as $row) {
    $row = trim($row);
    for($x = 0; $x < strlen($row); $x++) {
        if ($row[$x] === '#') {
            $elves[] = [$x, $y];
        }
    }
    $y++;
}

$needToRun = true;
$strategies = [[N, NE, NW], [S, SE, SW], [W, NW, SW], [E, NE, SE]];
$tenthRoundResult = 0;
$nobodyMoves = false;
$round = 0;
while($round < 100000 && (!$nobodyMoves)) {
    $round++;
    $mapOfElves = getMapOfElves($elves);

    $decisions = [];
    // loop over elves
    foreach ($elves as $e => $elf) {
        $elfAround = [];
        foreach([N, NE, E, SE, S, SW, W, NW] as $test) {
            if (isset($mapOfElves[$elf[1] + $test[1]][$elf[0] + $test[0]])) {
                $elfAround[$test[1]][$test[0]] = $mapOfElves[$elf[1] + $test[1]][$elf[0] + $test[0]];
            }
        }
        if (sizeof($elfAround) === 0) {
            // nobody around, we can stay
            continue;
        }
        // loop over strategies
        for($s = 0; $s < sizeof($strategies); $s++) {
            // test strategy - loop over its test directions
            foreach($strategies[$s] as $test) {
                if (isset($elfAround[$test[1]][$test[0]])) {
                    continue 2; // something is there, next strategy
                }
            }
            // strategy ok
            $decisions[$e] = $strategies[$s][0];
            break;
        }
    }

    // moves
    $newPositions = [];
    foreach ($decisions as $e => $decision) {
        $newPositions[$elves[$e][1] + $decision[1]][$elves[$e][0] + $decision[0]][] = $e;
    }
    $nobodyMoves = true;
    foreach ($newPositions as $y => $row) {
        foreach ($row as $x => $posInfo) {
            if (sizeof($posInfo) > 1) continue;
            // elf is moving
            $elfNum = $posInfo[0];
            $elves[$elfNum][0] += $decisions[$elfNum][0];
            $elves[$elfNum][1] += $decisions[$elfNum][1];
            $nobodyMoves = false;
        }
    }

    // final part of the round - shifting strategies
    $strategies[] = array_shift($strategies);

    // check if it's a 10th round
    if ($round === 10) {
        printf('First star: %d%s', getCount($elves), PHP_EOL);
    }
}

printf('Second star: %d%s', $round, PHP_EOL);

function getCount(array &$elves)
{
    [$mins, $maxs] = getRectangle($elves);
    return ($maxs[0] - $mins[0] + 1) * ($maxs[1] - $mins[1] + 1) - sizeof($elves);
}

function getRectangle(array &$elves): array
{
    $maxs = $mins = $elves[0];
    foreach($elves as $elf) {
        if ($elf[0] < $mins[0]) $mins[0] = $elf[0];
        if ($elf[1] < $mins[1]) $mins[1] = $elf[1];
        if ($elf[0] > $maxs[0]) $maxs[0] = $elf[0];
        if ($elf[1] > $maxs[1]) $maxs[1] = $elf[1];
    }
    return [$mins, $maxs];
}

function getMapOfElves(array &$elves): array
{
    $map = [];
    foreach($elves as $n => $elf) {
        $map[$elf[1]][$elf[0]] = $n;
    }
    return $map;
}

function printElves(array &$elves)
{
    [$mins, $maxs] = getRectangle($elves);
    $mapOfElves = getMapOfElves($elves);
    for($y = $mins[1]; $y <= $maxs[1]; $y++) {
        for($x = $mins[0]; $x <= $maxs[0]; $x++) {
            if (!isset($mapOfElves[$y][$x])) {
                echo '.';
            } else {
                echo '#';
            }
        }
        echo PHP_EOL;
    }
    echo PHP_EOL . PHP_EOL;
}