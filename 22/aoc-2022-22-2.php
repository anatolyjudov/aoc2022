<?php

declare(strict_types = 1);

const RIGHT = 0;
const DOWN  = 1;
const LEFT  = 2;
const UP    = 3;

const CLOCKWISE        = 'R';
const COUNTERCLOCKWISE = 'L';

const ROCK = '#';

$filedata = explode(PHP_EOL, file_get_contents('input.txt'));

$maze = [];
$borders = ['ver' => [], 'hor' => []];
$y = 0;
while($line = array_shift($filedata)) {
    if (trim($line) === '') break;
    for($x = 0; $x < strlen($line); $x++) {
        if ($line[$x] === ' ') continue;
        $maze[$y][$x] = $line[$x];
        //
        if (!isset($borders['hor'][$y])) {
            $borders['hor'][$y] = [RIGHT => $x, LEFT => $x];
        }
        if (!isset($borders['ver'][$x])) {
            $borders['ver'][$x] = [DOWN => $y, UP => $y];
        }
        if ($borders['hor'][$y][RIGHT] > $x) $borders['hor'][$y][RIGHT] = $x;
        if ($borders['hor'][$y][LEFT] < $x) $borders['hor'][$y][LEFT] = $x;
        if ($borders['ver'][$x][DOWN] > $y) $borders['ver'][$x][DOWN] = $y;
        if ($borders['ver'][$x][UP] < $y) $borders['ver'][$x][UP] = $y;
    }
    $y++;
}

$moves = [
    RIGHT => ['diff' => [ 1,  0]],
    LEFT  => ['diff' => [-1,  0]],
    UP    => ['diff' => [ 0, -1]],
    DOWN  => ['diff' => [ 0,  1]],
];

$commands = getCommands(trim(array_shift($filedata)));

$walker = [
    'facing' => RIGHT,
    'pos'    => [array_key_first($maze[0]), 0]
];

foreach($commands as $command) {

    // rotating commands
    {
        if ($command === CLOCKWISE) {
            $walker['facing']++;
            if ($walker['facing'] > UP) $walker['facing'] = RIGHT;
            continue;
        }

        if ($command === COUNTERCLOCKWISE) {
            $walker['facing']--;
            if ($walker['facing'] < RIGHT) $walker['facing'] = UP;
            continue;
        }
    }

    // move
    {
        $stepsLeft = (int)$command;
        while($stepsLeft > 0) {
            // get next position
            $nextPos = [
                $walker['pos'][0] + $moves[$walker['facing']]['diff'][0],
                $walker['pos'][1] + $moves[$walker['facing']]['diff'][1],
            ];
            $nextFacing = $walker['facing'];
            if (!isset($maze[$nextPos[1]][$nextPos[0]])) {
                // out of the map
                $next = wrapThroughTheEdge1($walker);
                [$nextPos, $nextFacing] = [$next[0], $next[1]];
            }
            // if it's a wall - stop
            if ($maze[$nextPos[1]][$nextPos[0]] === ROCK) {
                break;
            }
            $walker['pos'] = $nextPos;
            $walker['facing'] = $nextFacing;
            $stepsLeft--;
        }
    }
}

function wrapThroughTheEdge1(array $walker): array
{
    global $borders;

    $nextPosition = $walker['pos'];

    if ($walker['facing'] === RIGHT || $walker['facing'] === LEFT) {
        $nextPosition[0] = $borders['hor'][$walker['pos'][1]][$walker['facing']];
    }
    if ($walker['facing'] === DOWN || $walker['facing'] === UP) {
        $nextPosition[1] = $borders['ver'][$walker['pos'][0]][$walker['facing']];
    }

    $nextFacing = $walker['facing'];
    return [$nextPosition, $nextFacing];
}

$finalPassword = ($walker['pos'][1] + 1) * 1000 + ($walker['pos'][0] + 1) * 4 + $walker['facing'];
printf('First star: %d%s', $finalPassword, PHP_EOL);


function getCommands(string $commandsInput): array
{
    $commands = [];
    $currentNumeric = '';
    for ($i = 0; $i < strlen($commandsInput); $i++) {
        if (is_numeric($commandsInput[$i])) {
            $currentNumeric .= $commandsInput[$i];
            continue;
        }
        if (in_array($commandsInput[$i], [CLOCKWISE, COUNTERCLOCKWISE])) {
            if ($currentNumeric !== '') {
                $commands[] = $currentNumeric;
                $currentNumeric = '';
            }
            $commands[] = $commandsInput[$i];
        }
    }
    if ($currentNumeric !== '') {
        $commands[] = $currentNumeric;
    }

    return $commands;
}
