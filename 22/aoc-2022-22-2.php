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

$edges = [
    1 => [
        [LEFT, [[50, 99, -1], 50]],
        [UP, [100, [0, 49, -1]]]
    ],
    2 => [
        [DOWN, [49, [100, 149, 1]]],
        [RIGHT, [[50, 99, 1], 99]]
    ],
    3 => [
        [DOWN, [149, [50, 99, 1]]],
        [RIGHT, [[150, 199, 1], 49]]
    ],
    4 => [
        [RIGHT, [[0, 49, -1], 149]],
        [RIGHT, [[100, 149, 1], 99]]
    ],
    5 => [
        [LEFT, [[0, 49, -1], 50]],
        [LEFT, [[100, 149, 1], 0]]
    ],
    6 => [
        [UP, [0, [50, 99, 1]]],
        [LEFT, [[150, 199, 1], 0]]
    ],
    7 => [
        [UP, [0, [100, 149, 1]]],
        [DOWN, [199, [0, 49, 1]]]
    ],
];

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
                $next = wrapThroughTheEdge2($walker);
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
$finalPassword = ($walker['pos'][1] + 1) * 1000 + ($walker['pos'][0] + 1) * 4 + $walker['facing'];
printf('Star: %d%s', $finalPassword, PHP_EOL);

function wrapThroughTheEdge2(array $walker): array
{
    global $edges;

    // find edge and side
    for ($edgeNum = 1; $edgeNum <= 7; $edgeNum++) {
        for ($sideNum = 0; $sideNum <= 1; $sideNum++) {
            if (walkerIsGoingThroughTheEdge($walker, $edgeNum, $sideNum)) {
                $edge = $edges[$edgeNum];
                $side = $sideNum;
                break 2;
            }
        }
    }

    // calculate shift from the edge
    $shift = calculateEdgeShift($walker, $edge, $side);

    // other side
    $otherSide = $side === 0 ? 1 : 0;

    // calculate new coords
    $nextPosition = getPositionOnEdge($edge, $otherSide, $shift);

    // choose new facing
    switch ($edge[$otherSide][0]) {
        case LEFT:
            $nextFacing = RIGHT;
            break;
        case RIGHT:
            $nextFacing = LEFT;
            break;
        case UP:
            $nextFacing = DOWN;
            break;
        case DOWN:
            $nextFacing = UP;
            break;
        default:
            die('Unknown facing');
    }

    return [$nextPosition, $nextFacing];
}

function getPositionOnEdge(array $edge, int $side, int $shift): array
{
    if ($edge[$side][0] === LEFT || $edge[$side][0] === RIGHT) {
        $position[0] = $edge[$side][1][1]; // the same X
        if ($edge[$side][1][0][2] === 1) {
            $position[1] = $edge[$side][1][0][0] + $shift;
        } else {
            $position[1] = $edge[$side][1][0][1] - $shift;
        }
    }
    if ($edge[$side][0] === DOWN || $edge[$side][0] === UP) {
        $position[1] = $edge[$side][1][0]; // the same Y
        if ($edge[$side][1][1][2] === 1) {
            $position[0] = $edge[$side][1][1][0] + $shift;
        } else {
            $position[0] = $edge[$side][1][1][1] - $shift;
        }
    }

    return $position;
}

function calculateEdgeShift(array $walker, array $edge, int $side): int
{
    if ($walker['facing'] === LEFT || $walker['facing'] === RIGHT) {
        $edgeInterval = $edge[$side][1][0];
        if ($edgeInterval[2] === 1) {
            $shift = $walker['pos'][1] - $edgeInterval[0];
        } else {
            $shift = $edgeInterval[1] - $walker['pos'][1];
        }
    }

    if ($walker['facing'] === UP || $walker['facing'] === DOWN) {
        $edgeInterval = $edge[$side][1][1];
        if ($edgeInterval[2] === 1) {
            $shift = $walker['pos'][0] - $edgeInterval[0];
        } else {
            $shift = $edgeInterval[1] - $walker['pos'][0];
        }
    }

    return $shift;
}

function walkerIsGoingThroughTheEdge(array $walker, int $edgeNum, int $sideNum): bool
{
    global $edges;

    $side = $edges[$edgeNum][$sideNum];
    $walkerX = $walker['pos'][0];
    $walkerY = $walker['pos'][1];

    if ($walker['facing'] !== $side[0]) return false;

    if ($walker['facing'] === LEFT || $walker['facing'] === RIGHT) {
        return ($walkerX === $side[1][1])
            && ($walkerY >= $side[1][0][0])
            && ($walkerY <= $side[1][0][1]);
    }
    if ($walker['facing'] === UP || $walker['facing'] === DOWN) {
        return ($walkerY === $side[1][0])
            && ($walkerX >= $side[1][1][0])
            && ($walkerX <= $side[1][1][1]);
    }
    die('Unknown facing in walkerIsGoingThroughTheEdge');
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