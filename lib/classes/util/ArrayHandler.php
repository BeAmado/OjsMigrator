<?php

namespace BeAmado\OjsMigrator\Util;

class ArrayHandler
{
    public function union($arr1, $arr2)
    {
        if ($arr1 === null)
            return $this->union(array(), $arr2);

        if (!\is_array($arr1))
            return $this->union(array($arr1), $arr2);

        if ($arr2 === null)
            return $this->union($arr1, array());

        if (!\is_array($arr2))
            return $this->union($arr1, array($arr2));

        return \array_merge(
            \array_unique($arr1),
            \array_diff(
                \array_unique($arr2),
                \array_unique($arr1)
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
}
