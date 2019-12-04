<?php

namespace BeAmado\OjsMigrator\Util;

class ArrayHandler
{
    protected function isAssoc($arr)
    {
        foreach (\array_keys($arr) as $key) {
            if (!\is_numeric($key))
                return true;
        }

        return false;
    }

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

    /**
     * Checks if the arrays are equal, i.e. have the same data, not taking into 
     * consideration the order
     *
     * @param array $arr1
     * @param array $arr2
     * @return boolean
     */
    public function equals($arr1, $arr2)
    {
        if (!\is_array($arr1) || !\is_array($arr2))
            return false;

        if (\count($arr1) !== \count($arr2))
            return false;

        return \count(\array_intersect($arr1, $arr2)) === \count($arr1);
    }
}
