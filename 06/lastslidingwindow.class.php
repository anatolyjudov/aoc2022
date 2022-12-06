<?php


class Last
{
    protected $data = [];
    protected $c = 0;
    protected $size = 0;

    protected $counts = [];

    /**
     * @param int $size
     */
    public function __construct(int $size = 4)
    {
        $this->size = $size;
    }

    /**
     * @return bool
     */
    public function isDifferent(): bool
    {
        if (count($this->counts) < $this->size) {
            return false;
        }

        return true;
    }

    /**
     * @param $symbol
     * @return $this
     */
    public function add($symbol): void
    {
        // save the new symbol and remember the old one which had been there
        $droppedSymbol = $this->data[$this->c] ?? null;
        $this->data[$this->c] = $symbol;

        // increase the counter for the new symbol
        if (empty($this->counts[$symbol])) {
            $this->counts[$symbol] = 1;
        } else {
            $this->counts[$symbol]++;
        }

        // decrease the counter for the old symbol
        if (!empty($droppedSymbol)) {
            if ($this->counts[$droppedSymbol] == 1) {
                unset($this->counts[$droppedSymbol]);
            } else {
                $this->counts[$droppedSymbol]--;
            }
        }

        // move data storage pointer
        $this->c++;
        if ($this->c === $this->size) {
            $this->c = 0;
        }
    }
}