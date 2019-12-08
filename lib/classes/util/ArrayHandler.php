<?php

namespace BeAmado\OjsMigrator\Util;

class ArrayHandler
{
    /**
     * Checks if the array is associative.
     *
     * @param array $arr
     * @return boolean
     */
    protected function isAssoc($arr)
    {
        foreach (\array_keys($arr) as $key) {
            if (!\is_numeric($key))
                return true;
        }

        return false;
    }

    /**
     * Gets the union of two arrays.
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public function union($arr1, $arr2)
    {
        if ($arr1 === null)
            return $this->union(array(), $arr2);

        if (\is_a($arr1, \BeAmado\OjsMigrator\MyObject::class))
            return $this->union($arr1->toArray(), $arr2);

        if (!\is_array($arr1))
            return $this->union(array($arr1), $arr2);

        if ($arr2 === null)
            return $this->union($arr1, array());

        if (\is_a($arr2, \BeAmado\OjsMigrator\MyObject::class))
            return $this->union($arr1, $arr2->toArray());

        if (!\is_array($arr2))
            return $this->union($arr1, array($arr2));

        return \array_merge(
            $this->isAssoc($arr1) ? $arr1 : \array_unique($arr1),
            \array_diff(
                $this->isAssoc($arr2) ? $arr2 : \array_unique($arr2),
                $this->isAssoc($arr1) ? $arr1 : \array_unique($arr1)
            ) ?: array()
        );
    }

    public function unionN($arrays)
    {
        if (!\is_array($arrays))
            return array();
        
        return \array_reduce($arrays, array($this, 'union'));
    }

    public function isLast($elem, $arr)
    {
        if (!\in_array($elem, $arr))
            return;

        return \array_search($elem, $arr) === (\count($arr) - 1);
    }

    protected function equalsAssoc($arr1, $arr2)
    {
        foreach ($arr1 as $key => $value) {
            if (!\array_key_exists($key, $arr2))
                return false;

            if ($arr2[$key] != $value)
                return false;
        }

        return true;
    }

    protected function equalsIndex($arr1, $arr2)
    {
        foreach ($arr1 as $value) {
            if (!\in_array($value, $arr2))
                return false;
        }

        return true;
    }

    /**
     * Checks if the arrays are equal, i.e. have the same data, not taking into 
     * consideration the order.
     *
     * @param array $arr1
     * @param array $arr2
     * @return boolean
     */
    public function equals($arr1, $arr2)
    {
        if (empty($arr1) || empty($arr2))
            return false;

        if (!\is_array($arr1) || !\is_array($arr2))
            return false;

        if (\count($arr1) !== \count($arr2))
            return false;

        if ($this->isAssoc($arr1) && $this->isAssoc($arr2))
            return $this->equalsAssoc($arr1, $arr2);

        if (!$this->isAssoc($arr1) && !$this->isAssoc($arr2))
            return $this->equalsIndex($arr1, $arr2);

        return false;
    }

    /**
     * Checks if two multidimensional array are equivalent, i.e their subarrays
     * are equal not considering the order.
     *
     * @param array $arr1
     * @param array $arr2
     * @return boolean
     */
    public function areEquivalent($arr1, $arr2)
    {
        if (!\is_array($arr1) || !\is_array($arr2))
            return false;

        if (\count($arr1) !== \count($arr2))
            return false;

        $arr2Copy = $arr2;

        for ($i = 0; $i < \count($arr1); $i++) {
            $found = false;
            for ($j = 0; $j < \count($arr2Copy); $j++) {
                if ($this->equals($arr1[$i], $arr2Copy[$j])) {
                    $found = true;
                    \array_splice($arr2Copy, $j, 1); // removes the element at index $j
                    break;
                }
            }
            if (!$found) {
                // Registry::get('MemoryManager')->destroy($arr2Copy);
                // unset($arr2Copy);
                return false;
            }
        }

        return true;
    }
}
