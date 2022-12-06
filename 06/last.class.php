<?php

class Last
{
    protected $data = [];
    protected $c = 0;
    protected $size = 0;

    /**
     * @param $size
     */
    public function __construct($size = 4) {
        $this->size = $size;
    }

    /**
     * @return bool
     */
    public function isDifferent(): bool
    {
        if (count($this->data) < $this->size) {
            return false;
        }

        $result = true;
        for($i = 0; $i < $this->size - 1; $i++) {
            for($o = $i + 1; $o < $this->size; $o++) {
                if ($this->data[$i] === $this->data[$o]) {
                    $result = false;
                    break 2;
                }
            }
        }

        return $result;
    }

    /**
     * @param $symbol
     * @return $this
     */
    public function add($symbol): self
    {
        $this->data[$this->c] = $symbol;
        $this->move();
        return $this;
    }

    /**
     * @return void
     */
    protected function move(): void
    {
        $this->c++;
        if ($this->c === $this->size) {
            $this->c = 0;
        }
    }
}