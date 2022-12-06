<?php

$filename = 'input.txt';
$fp       = fopen($filename, 'r');

// read crates info
do {
    $cratesLine = fgets($fp);

    if ($cratesLine[1] === '1') {
        fgets($fp);
        break;
    }

    $columnsCount = strlen($cratesLine) / 4;

    if (!isset($crates)) {
        $crates = initCrates($columnsCount);
    }

    for($i = 0; $i < $columnsCount; $i++) {
        if ($cratesLine[$i * 4 + 1] === ' ') {
            continue;
        }
        $crates[$i][] = $cratesLine[$i * 4 + 1];
    }
} while (!feof($fp));

revertCrates($crates);

$crates2 = $crates;

// read instructions
do {

    $instruction = trim(fgets($fp));
    $commandData = explode(' ', $instruction);

    if ($commandData[0] !== 'move') {
        die('unknown command: ' . $instruction);
    }

    doMoveFromTo($crates, (int)$commandData[1], (int)$commandData[3], (int)$commandData[5]);
    doMoveStackFromTo($crates2, (int)$commandData[1], (int)$commandData[3], (int)$commandData[5]);

} while (!feof($fp));

echo 'First star: ' . readTops($crates) . "\r\n";
echo 'Second star: ' . readTops($crates2) . "\r\n";

// functions

function initCrates($size): array
{
    return array_fill(0, $size, []);
}

function revertCrates(&$crates): void
{
    for ($i = 0; $i < sizeof($crates); $i++) {
        $crates[$i] = array_reverse($crates[$i]);
    }
}

function doMoveFromTo(&$crates, $amount, $from, $to): void
{
    $from--;
    $to--;

    for($i = 0; $i < $amount; $i++) {
        $crate = array_pop($crates[$from]);
        $crates[$to][] = $crate;
    }
}

function doMoveStackFromTo(&$crates, $amount, $from, $to): void
{
    $from--;
    $to--;

    $stack = [];
    for($i = 0; $i < $amount; $i++) {
        $stack[] = array_pop($crates[$from]);
    }
    $stack = array_reverse($stack);
    array_push($crates[$to], ...$stack);
}

function readTops(&$crates): string
{
    $result = '';
    foreach($crates as $stack) {
        $result .= $stack[sizeof($stack) - 1];
    }
    return $result;
}