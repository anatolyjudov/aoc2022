<?php

declare(strict_types = 1);

class Reader
{
    protected $fp;

    public function __construct($filename)
    {
        $this->fp = fopen($filename, 'r');
    }

    public function next(): string
    {
        return rtrim(fgets($this->fp));
    }

    public function end(): bool
    {
        return feof($this->fp);
    }
}

class Commander extends Reader
{
    public function get(): ?Command
    {
        if ($this->end()) {
            return null;
        }
        [$move, $count] = explode(' ', $this->next());
        return new Command($move, $count);
    }
}

class Command
{
    public $move;
    public $count;

    public function __construct($move, $count)
    {
        $this->move  = $move;
        $this->count = $count;
    }
}

class Point
{
    public int $x = 0;
    public int $y = 0;

    public function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }
}

class Points
{
    /**
     * @var array Point[]
     */
    protected array $points = [];

    protected array $unique = [];

    protected function getKey(Point $p)
    {
        return implode(',', [$p->x, $p->y]);
    }

    public function add(Point $p)
    {
        $this->points[] = $p;

        $k                = $this->getKey($p);
        $this->unique[$k] = ($this->unique[$k] ?? 0) + 1;
    }

    public function countUnique()
    {
        return sizeof($this->unique);
    }
}

class Movable
{
    protected array $moves = [
        'U' => [ 0,  1],
        'D' => [ 0, -1],
        'L' => [-1,  0],
        'R' => [ 1,  0]
    ];

    protected function isValid($move): bool
    {
        return !empty($this->moves[$move]);
    }

    protected function newPos($move, Point $pos): Point
    {
        $shift = $this->moves[$move];
        return new Point(
            $pos->x + $shift[0],
            $pos->y + $shift[1]
        );
    }

    protected function newPosPull(Point $to, Point $pos): Point
    {
        $shift = [0, 0];
        $dX    = $to->x - $pos->x;
        $dY    = $to->y - $pos->y;

        if ((abs($dX) > 1) || (abs($dY) > 1)) {
            $shift[0] = $dX === 0 ? 0 : abs($dX) / ($dX);
            $shift[1] = $dY === 0 ? 0 : abs($dY) / ($dY);
        }

        return new Point(
            $pos->x + $shift[0],
            $pos->y + $shift[1]
        );
    }
}

class RopeKnot extends Movable
{
    public Point $pos;

    /**
     * @var Points
     */
    public Points $trajectory;

    public function __construct(Point $pos)
    {
        $this->pos     = $pos;
        $this->trajectory = new Points();

        $this->trajectory->add($pos);
    }
}

class RopeHead extends RopeKnot
{
    public function move($move) {
        if (!$this->isValid($move)) die();

        $this->pos = $this->newPos($move, $this->pos);
        $this->trajectory->add($this->pos);
    }
}

class RopePullable extends RopeKnot
{
    public function pullTo($point) {
        $this->pos = $this->newPosPull($point, $this->pos);
        $this->trajectory->add($this->pos);
    }
}

class ShortRope
{
    public RopeHead     $head;
    public RopePullable $tail;

    public function __construct()
    {
        $this->head = new RopeHead(new Point(0, 0));
        $this->tail = new RopePullable($this->head->pos);
    }

    public function moveHead($move)
    {
        $this->head->move($move);
        $this->tail->pullTo($this->head->pos);
    }
}

class Rope
{
    public RopeHead $head;

    /**
     * @var array RopePullable[]
     */
    public array $body;

    public function __construct(int $length)
    {
        if ($length < 2) {
            die('Please provide length of at least 2 knots');
        }
        $this->head = new RopeHead(new Point(0, 0));
        for($i = 0; $i < $length - 1; $i++) {
            $this->body[] = new RopePullable($this->head->pos);
        }
    }

    public function moveHead($move)
    {
        $this->head->move($move);
        $this->body[0]->pullTo($this->head->pos);
        for($i = 1; $i < sizeof($this->body); $i++) {
            $this->body[$i]->pullTo($this->body[$i - 1]->pos);
        }
    }

    public function getTail(): RopeKnot
    {
        return $this->body[sizeof($this->body) - 1];
    }
}

$shortRope = new ShortRope();
$fullRope  = new Rope(10);
$commander = new Commander('input.txt');

while(!empty($command = $commander->get())) {
    for($i = 0; $i < $command->count; $i++) {
        $shortRope->moveHead($command->move);
        $fullRope->moveHead($command->move);
    }
}

echo 'First star: ' . $shortRope->tail->trajectory->countUnique() . "\r\n";
echo 'Second star: ' . $fullRope->getTail()->trajectory->countUnique() . "\r\n";