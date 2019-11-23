<?php

namespace BeAmado\OjsMigrator\Util;

class RangeHandler
{
    /**
     * Returns a string representing a range including the boundaries.
     *
     * @param integer | string $lowerBound
     * @param integer | string $upperBound
     * @return string
     */
    public function formRange($lowerBound, $upperBound)
    {
        if (
            !\is_numeric($lowerBound) ||
            !\is_numeric($upperBound)
        )
            return;

        if ($lowerBound < 1 || $lowerBound > $upperBound)
            $lowerBound = 1;

        if ($upperBound < 1 || $upperBound < $lowerBound)
            return;

        return '' . ((int) $lowerBound) . '-' . ((int) $upperBound);
    }

    /**
     * Gets the base range for the specified number. The base range is the
     * renge from 1 to the smallest power of ten that is greater than the
     * number.
     *
     * @param integer $number
     * @return string
     */
    public function baseRange($number)
    {
        return $this->powerOfTenRange($number, \strlen('' . $number));
    }

    /**
     * Creates range for the specified number with the variation in the
     * specified power of ten.
     *
     * Examples:
     * 1. powerOfTenRange(2345, 2) => 2301-2400 (variation in the 100's)
     * 2. powerOfTenRange(12522, 3) => 12001-13000 (variation in the 1000's)
     * 3. powerOfTenRange(123019, 1) => 123011-123020 (variation in the 10's)
     *
     * @param integer $number
     * @param integer $power
     * @return string
     */
    public function powerOfTenRange($number, $power)
    {
        $m = \pow(10, $power);

        if ($power > $number)
            return $this->formRange(1, $m);

        $prefix = ((int) \floor($number / (10*$m))) * 10*$m;
        $suffix = $number % (10*$m);

        if ($suffix % $m === 0)
            return $this->formRange(
                $prefix + $suffix - $m + 1,
                $prefix + $suffix
            );
        
        return $this->formRange(
            $prefix + (((int) \floor($suffix / $m)) * $m) + 1,
            $prefix + ((int) \ceil($suffix / $m)) * $m
        );
    }

    /**
     * Creates a structure of ranges for the specified number.
     *
     * Examples: 
     * 1. 367 => array('1-1000', '301-400', '361-370', '367')
     * 2. 29372 => array('1-100000', '20001-30000', '29001-30000', 
     *                   '29301-29400', '29371-39380', '29372')
     * 3. 12 => array('1-100', '11-20', '12')
     *
     * @param integer $n
     * @return array
     */
    public function rangesStructure($n)
    {
        $ranges = array($this->baseRange($n));

        for ($p = \strlen('' . $n) - 1; $p > 0; $p--) {
            $ranges[] = $this->powerOfTenRange($n, $p);
        }

        $ranges[] = $n;

        return $ranges;
    }

    /**
     * Returns the string representation of the ranges structure.
     *
     * Examples: 
     * 1. 367 => 1-1000/301-400/361-370/367
     * 2. 29372 => 1-100000/20001-30000/29001-30000/29301-29400/29371-39380/29372
     * 3. 12 => 1-100/11-20/12
     *
     * @param integer $n
     * @return array
     */
    public function rangesString($n)
    {
        return \implode(
            \BeAmado\OjsMigrator\DIR_SEPARATOR, 
            $this->rangesStructure($n)
        );
    }

    protected function lowerBoundary($rangeStr)
    {
        return \explode('-', $rangeStr)[0];
    }

    protected function upperBoundary($rangeStr)
    {
        return \explode('-', $rangeStr)[1];
    }

    /**
     * Returns the largest of 2 ranges.
     *
     * @param string $range1
     * @param string $range2
     * @return string
     */
    public function largestRange($range1, $range2)
    {
        if ($this->lowerBoundary($range1) != $this->lowerBoundary($range2))
            return;

        if ($this->upperBoundary($range1) > $this->upperBoundary($range2))
            return $range1;
        else
            return $range2;
    }

    /**
     * Returns the smallest of 2 ranges.
     *
     * @param string $range1
     * @param string $range2
     * @return string
     */
    public function smallestRange($range1, $range2)
    {
        if ($this->largestRange($range1, $range2) == $range1)
            return $range2;
        else
            return $range1;
    }

    /**
     * Gets the diff between two ranges.
     *
     * @param string $range1
     * @param string $range2
     * @return string
     */
    public function rangesDiff($range1, $range2)
    {
        if ($range1 == $range2)
            return;

        if ($this->largestRange($range1, $range2) == $range2)
            return $this->rangesDiff($range2, $range1);

        $largestPower = \strlen('' . $this->upperBoundary($range1)) - 1;
        $smallestPower = \strlen('' . $this->upperBoundary($range2)) - 1;

        $ranges = array();
        for ($p = $largestPower; $p > $smallestPower; $p--) {
            $ranges[] = $this->formRange(1, \pow(10, $p));
        }

        return \implode(\BeAmado\OjsMigrator\DIR_SEPARATOR, $ranges);
    }

}
