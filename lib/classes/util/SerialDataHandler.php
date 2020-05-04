<?php

namespace BeAmado\OjsMigrator\Util;

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

    protected function serializedTypes()
    {
        return array(
            'n' => 'null',
            'b' => 'bool',
            'i' => 'int',
            'd' => 'double',
            's' => 'string',
            'a' => 'array',
        );
    }

    protected function getSerializedType($str)
    {
        if (!\is_string($str))
            return false;

        if (\substr($str, 0, 1) === 'a' && \substr($str, -1) === '}')
            return $this->serializedTypes()['a'];

        if (
            \substr($str, -1) === ';' &&
            \array_key_exists(
                \strtolower(\substr($str, 0, 1)),
                $this->serializedTypes()
            )
        )
            return $this->serializedTypes()[\strtolower(\substr($str, 0, 1))];
    }

    protected function unserializeNull($str)
    {
        if ($str === $this->nullRepr())
            return null;

        return false;
    }

    protected function unserializeBool($str)
    {
        if ($str === $this->boolRepr(true))
            return true;

        if ($str === $this->boolRepr(false))
            return false;
    }

    protected function unserializeInteger($str)
    {
        return (int) \explode(':', \substr($str, 0, -1))[1];
    }

    protected function unserializeDouble($str)
    {
        return (float) \explode(':', \substr($str, 0, -1))[1];
    }

    protected function unserializeString($str)
    {
        return \trim(\explode(':', \substr($str, 0, -1))[2], '"');
    }

    protected function getSerializedArraySize($str)
    {
        return (int) \explode(':', $str)[1];
    }

    protected function getArrayData($str)
    {
        $index = \strpos($str, '{');
        return array(
            'size' => $this->getSerializedArraySize(\substr($str, 0, $index)),
            'payload' => \substr($str, $index + 1, -1), // remove the curly braces {}
        );
    }

    protected function unserializeArrayData($data)
    {
        $arr = array();
        $offset = 0;
        for ($i = 0; $i < $data['size']; $i++) {
            $end = \strpos($data['payload'], ';', $offset);
            $key = $this->manuallyUnserialize(\substr(
                $data['payload'],
                $offset,
                $end - $offset + 1
            ));

            $offset = $end + 1;
            // has to be modified if the data is either a string or an array
            $valueType = \substr($data['payload'], $offset, 1);
            $match = ';';
            if ($valueType === 'a')
                $match = '}';
            else if ($valueType === 's')
                $match = '";';

            $end = \strpos($data['payload'], $match, $offset);
//            $interestStr = \substr(
//                $data['payload'],
//                $offset,
//                $end - $offset + 1 + ((int) ($valueType === 's'))
//            );
//            var_dump($interestStr);
            $value = $this->manuallyUnserialize(\substr(
                $data['payload'],
                $offset,
                $end - $offset + 1 + ((int) ($valueType === 's'))
            ));

            $offset = $end + 1 + ((int) ($valueType === 's'));

            $arr[$key] = $value;

        }

        return $arr;
    }

    protected function unserializeArray($str)
    {
        return $this->unserializeArrayData($this->getArrayData($str));
    }

    public function manuallyUnserialize($str)
    {
        switch($this->getSerializedType($str)) {
            case 'null':
                return $this->unserializeNull($str);
            case 'bool':
                return $this->unserializeBool($str);
            case 'int':
                return $this->unserializeInteger($str);
            case 'double':
                return $this->unserializeDouble($str);
            case 'string':
                return $this->unserializeString($str);
            case 'array':
                return $this->unserializeArray($str);
        }

        return false;
    }

    protected function getStringBorderIndexes($data, $offset = 0)
    {
        $begin = \strpos($data, 's:', $offset);
        if ($begin === false)
            return null;

        $end = \strpos($data, '";', $begin + 2);
        if ($end === false)
            return null;

        return array($begin, $end + 1);
    }

    public function fixSerializedStrings()
    {
        // 1 - extract the serialized strings

        // 2 - slice the original string into pieces without the serialized strings

        // 3 - fix the serialized strings

        // 4 - glue bak together intertwining the pieces and the fixed strings
    }
}
