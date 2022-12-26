<?php

declare(strict_types = 1);

const RIGHT = 0;
const DOWN  = 1;
const LEFT  = 2;
const UP    = 3;

const CLOCKWISE        = 'R';
const COUNTERCLOCKWISE = 'L';

$filedata = explode(PHP_EOL, file_get_contents('input.txt'));

$mazeYX = $mazeXY = [];
$y = 0;
while($line = array_shift($filedata)) {
    if (trim($line) === '') break;
    for($x = 0; $x < strlen($line); $x++) {
        if ($line[$x] === ' ') continue;
        $mazeYX[$y][$x] = $mazeXY[$x][$y] = $line[$x];
    }
    $y++;
}

$facing = [
    RIGHT => ['diff' => [ 1,  0], 'map' => &$mazeYX],
    LEFT  => ['diff' => [-1,  0], 'map' => &$mazeYX],
    UP    => ['diff' => [ 0, -1], 'map' => &$mazeXY],
    DOWN  => ['diff' => [ 0,  1], 'map' => &$mazeXY],
];

$commands = getCommands(trim(array_shift($filedata)));

$walker = [
    'facing' => RIGHT,
    'pos'    => [array_key_first($mazeYX[0]), 0]
];

var_dump($walker);

foreach($commands as $command) {


    var_dump($command);

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

    // move
    if ($facing[$walker['facing']]['diff'][1] === 0) {
        $pathLine = $mazeYX[$walker['pos'][1]];
        $positionOnPathLine = $walker['pos'][0];
        $directionOnThePathLine = $facing[$walker['facing']]['diff'][0];
    } else {
        $pathLine = $mazeXY[$walker['pos'][0]];
        $positionOnPathLine = $walker['pos'][1];
        $directionOnThePathLine = $facing[$walker['facing']]['diff'][1];
    }
    $nextPositionOnPathLine = moveAlongTheLine($pathLine, $positionOnPathLine, $directionOnThePathLine, (int)$command);
    if ($facing[$walker['facing']]['diff'][1] === 0) {
        $walker['pos'][0] = $nextPositionOnPathLine;
    } else {
        $walker['pos'][1] = $nextPositionOnPathLine;
    }

    var_dump($walker);

}

$finalPassword = ($walker['pos'][1] + 1) * 1000 + ($walker['pos'][0] + 1) * 4 + $walker['facing'];
printf('First star: %d%s', $finalPassword, PHP_EOL);

function moveAlongTheLine(array $pathLine, int $startPosition, int $direction, int $maxLength): int
{
    $currentPosition = $startPosition;
    for($s = 0; $s < $maxLength; $s++) {
        $nextPosition = $currentPosition + $direction;
        if ($nextPosition < array_key_first($pathLine)) {
            $nextPosition = array_key_last($pathLine);
        }
        if ($nextPosition > array_key_last($pathLine)) {
            $nextPosition = array_key_first($pathLine);
        }
        if ($pathLine[$nextPosition] === '#') {
            break;
        }
        $currentPosition = $nextPosition;
    }
    printf( 'moved from %d to %d %s', $startPosition, $currentPosition, PHP_EOL);
    return $currentPosition;
}

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
