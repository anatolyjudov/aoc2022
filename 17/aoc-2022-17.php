<?php

declare(strict_types = 1);

//ini_set('memory_limit', '2048M');

class Jets
{
    protected int $pointer;
    protected array $jets;

    public int $jetCount;

    public function __construct($filename)
    {
        $this->jets = str_split(trim(file_get_contents($filename)));
        $this->pointer = 0;
        $this->jetCount = sizeof($this->jets);
    }

    public function get()
    {
        $res = $this->jets[$this->pointer];
        $this->pointer++;
        if ($this->pointer === sizeof($this->jets)) {
            $this->pointer = 0;
        }
        return $res;
    }
}

class Dispatcher
{
    protected static array $figures = [
        [
            0b0011110
        ],
        [
            0b0001000,
            0b0011100,
            0b0001000
        ],
        [
            0b0000100,
            0b0000100,
            0b0011100
        ],
        [
            0b0010000,
            0b0010000,
            0b0010000,
            0b0010000
        ],
        [
            0b0011000,
            0b0011000
        ]
    ];

    private int $pointer;

    public int $figuresCount;

    public function __construct()
    {
        $this->pointer = 0;
        $this->figuresCount = sizeof(self::$figures);
    }

    public function get()
    {
        $res = self::$figures[$this->pointer];
        $this->pointer++;
        if ($this->pointer === sizeof(self::$figures)) {
            $this->pointer = 0;
        }
        return $res;
    }
}

class Cup
{
    public $magicTop = 63;

    protected array $rows = [];

    protected array $figure;
    protected int $position;

    public int $stored = 0;

    public function flat(): bool
    {
        return ($this->rows[sizeof($this->rows) - 1]) === 0b1111111;
    }

    public function compact()
    {
        $this->stored += sizeof($this->rows) - $this->magicTop;
        $this->rows = array_slice($this->rows, -1 * $this->magicTop);
    }

    public function place($figure)
    {
        $this->figure = $figure;
        $this->position = $this->getHeight() + 3;
    }

    public function blow(string $d)
    {
        $newFigure = [];
        if ($d === '<') {
            foreach($this->figure as $row) {
                if (($row & 0b1000000) !== 0) return;
                $newFigure[] = $row << 1;
            }
        } elseif ($d === '>') {
            foreach($this->figure as $row) {
                if (($row & 1) !== 0) return;
                $newFigure[] = $row >> 1;
            }
        }
        if (!$this->testFigure($newFigure, $this->position)) {
            return;
        }
        $this->figure = $newFigure;
    }

    public function moveDown(): bool
    {
        if ($this->position === 0) return false;
        if (!$this->testFigure($this->figure, $this->position - 1)) {
            return false;
        }
        $this->position--;

        return true;
    }

    public function testFigure($figure, $onPosition): bool
    {
        for($figureRow = sizeof($figure) - 1; $figureRow >= 0; $figureRow--) {
            $cupRow = $onPosition + sizeof($figure) - 1 - $figureRow;
            if (empty($this->rows[$cupRow])) continue;
            if (($this->rows[$cupRow] & $figure[$figureRow]) !== 0) {
                return false;
            }
        }

        return true;
    }

    public function getHeight(): int
    {
        return sizeof($this->rows);
    }

    public function rest()
    {
        for($i = 0; $i < sizeof($this->figure); $i++) {
            $cupRow = $this->position + $i;
            $figureRow = sizeof($this->figure) - 1 - $i;
            if (empty($this->rows[$cupRow])) {
                $this->rows[$cupRow] = 0;
            }
            $this->rows[$cupRow] |= $this->figure[$figureRow];
        }
        $this->figure = [];
        $this->position = $this->getHeight();
    }

    public function countAll()
    {
        return $this->stored + $this->getHeight();
    }

    public function print()
    {
        $top = ($this->getHeight() > $this->position) ? $this->getHeight() : $this->position;
        $from = $top + sizeof($this->figure);
        for ($i = $from; $i >= 0; $i--) {
            $cupValue = $this->rows[$i] ?? 0;
            $figValue = $this->figure[sizeof($this->figure) - $i + $this->position - 1] ?? 0;
            printf('|%07b| |%07b| %s', $cupValue,  $figValue, PHP_EOL);
        }

        echo '|-------|' . PHP_EOL . PHP_EOL;
    }
}

$jets = new Jets('input.txt');
$dispatcher = new Dispatcher();
$cup = new Cup();

$ololo = 1000000000000;
$figureNum = 0;

$lastFlat = 0;
$lastFlatHeight = 0;

while($figureNum < $ololo) {

    $figureNum++;

    // drop figure
    $cup->place($dispatcher->get());
    do {
        $cup->blow($jets->get());
    } while($cup->moveDown());
    $cup->rest();

    if ($figureNum === 2022) {
        printf('First star: %d%s', $cup->getHeight(), PHP_EOL);
    }

    if ($cup->flat()) {
        echo 'cup is flat after figure: ' . $figureNum
            . ' diff is ' . ($figureNum - $lastFlat)
            . ' height diff is ' . ($cup->getHeight() - $lastFlatHeight)
            . PHP_EOL;
        if ($lastFlat !== 0) {
            $blockDiff = $figureNum - $lastFlat;
            $blockHeight = $cup->getHeight() - $lastFlatHeight;

            $fullBlocksLeft = (int) floor(($ololo - $figureNum) / $blockDiff);
            $fullBlocksHeight = $fullBlocksLeft * $blockHeight;
            echo 'we can drop ' . $fullBlocksLeft . ' blocks with total height ' . $fullBlocksHeight . PHP_EOL;
            $figureNum += $fullBlocksLeft * $blockDiff;
            $cup->stored += $fullBlocksHeight;
        }
        $lastFlat = $figureNum;
        $lastFlatHeight = $cup->getHeight();
    }
}

printf('Second star: %d%s', $cup->countAll(), PHP_EOL);

