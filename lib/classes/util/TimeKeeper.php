<?php

namespace BeAmado\OjsMigrator\Util;

class TimeKeeper
{
    /**
     * Gets the timestamp with milisseconds
     */
    public function now()
    {
        return \array_reduce(
            \explode(' ', \microtime()),
            function($a, $b) {
                return $a + $b;
            }
        );
    }

    /**
     * Returns the elapsed time since the specified timestamp with milissecond
     * precision.
     *
     * @param float $origin - A timestamp with milissecond
     * @return float
     */
    public function elapsedTime($origin)
    {
        return $this->now() - $origin;
    }

    /**
     * Waits for the specified amount in milisseconds
     *
     * @param integer $ms
     */
    public function wait($ms)
    {
        $begin = $this->now();

        while ($this->elapsedTime($begin) < ($ms / 1000)) {
            // just wait
        }

        unset($begin);
    }
}
