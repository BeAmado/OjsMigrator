<?php

namespace BeAmado\OjsMigrator\Util;
use BeAmado\OjsMigrator\Registry;

class SerialDataHandler
{
    public function serializationIsOk($str)
    {
        return \is_string($str) && @\unserialize($str) !== false;
    }

    public function manuallySerialize($data, $depth = 0)
    {
        if (\is_null($data))
            return $this->NullRepr();
        if (\is_bool($data))
            return $this->boolRepr($data);
        if (\is_int($data))
            return $this->intRepr($data);
        if (\is_double($data))
            return $this->doubleRepr($data);
        if (\is_string($data))
            return $this->stringRepr($data);
        if (\is_array($data) && $depth <= 10)
            return $this->arrayRepr($data, $depth + 1);
    }

    protected function nullRepr()
    {
        return 'N;';
    }

    protected function boolRepr($data)
    {
        return $data ? 'b:1;' : 'b:0;';
    }

    protected function intRepr($data)
    {
        return 'i:' . $data . ';';
    }

    protected function doubleRepr($data)
    {
        return 'd:' . $data . ';';
    }

    protected function stringRepr($data)
    {
        return 's:' . \strlen($data) . ':"' . $data . '";';
    }

    protected function assoc2index($arr)
    {
        $indexed = array();
        foreach ($arr as $key => $value) {
            $indexed[] = array('key' => $key, 'value' => $value);
        }
        return $indexed;
    }

    protected function index2assoc($arr)
    {
        $assoc = array();
        foreach ($arr as $data) {
            $assoc[$data['key']] = $data['value'];
        }
        return $assoc;
    }

    protected function encodeArray($data, $depth)
    {
        return \array_reduce($this->assoc2index($data), function($carry, $el) {
            return array(
                $carry[0],
                \implode('', array(
                    $carry[1],
                    $this->manuallySerialize($el['key'], $carry[0]),
                    $this->manuallySerialize($el['value'], $carry[0]),
                ))
            );
        }, array($depth, ''))[1];
    }

    protected function arrayRepr($data, $depth = 0)
    {
        return \implode(':', array(
            'a',
            \count($data),
            \implode('', array(
                '{',
                $this->encodeArray($data, $depth),
                '}',
            )),
        ));
    }
}
