<?php

namespace BeAmado\OjsMigrator\Util;
use BeAmado\OjsMigrator\Registry;

class CaseHandler
{
    protected function getCaseName($case)
    {
        $str = \strtolower(\str_replace('_', '', $case));

        if (\strpos($str, 'case') !== false) {
            return \substr($str, 0, \strpos($str, 'case'));
        }

        return $str;
    }

    protected function explodeSnakeCase($str)
    {
        return \explode('_', $str);
    }

    protected function explodePascalCase($str)
    {
        $upperChars = array();

        foreach (\str_split($str) as $char) {
            if (!\is_numeric($char) && \strtoupper($char) === $char)
                $upperChars[] = $char;
        }

        foreach (\array_unique($upperChars) as $char) {
            $str = \str_replace($char, '_' . \strtolower($char), $str);
        }

        if (\substr($str, 0, 1) === '_')
            $str = \substr($str, 1);
        
        return $this->explodeSnakeCase($str);
    }

    protected function explodeCamelCase($str)
    {
        return $this->explodePascalCase(\ucfirst($str));
    }


    protected function explodeCase($str, $case)
    {
        switch($this->getCaseName($case)) {
            case 'snake':
                return $this->explodeSnakeCase($str);

            case 'camel':
                return $this->explodeCamelCase($str);

            case 'pascal':
                return $this->explodePascalCase($str);

            default:
                return array(\strtolower($str));
        }
    }

    protected function implodePascalCase($pieces)
    {
        return \implode('', \array_map('ucfirst', $pieces));
    }

    protected function implodeCase($pieces, $case)
    {
        switch ($this->getCaseName($case)) {
            case 'snake':
                return \implode('_', $pieces);

            case 'pascal':
                return $this->implodePascalCase($pieces);

            case 'camel':
                return \lcfirst($this->implodePascalCase($pieces));

            case 'lower':
                return \strtolower(\implode('', $pieces));

            case 'upper':
                return \strtoupper(\implode('', $pieces));

            default:
                return \implode('', $pieces);
        }
    }



    /**
     * Transforms the string from one type of case to another.
     * For example transform from snake_case to PascalCase
     *
     * @param string $str
     * @param string $from
     * @param string $to
     * @return string
     */
    public function transformCaseFromTo($from, $to, $str)
    {
        return $this->implodeCase(
            $this->explodeCase($str, $from),
            $to
        );
    }

    /**
     * Identifies in which case the string is.
     *
     * @param string $str
     * @return string
     */
    protected function identifyCase($str)
    {
        if (\strpos($str, '_') !== false)
            return 'snake_case';
        
        if (\strtoupper($str) === $str)
            return 'UPPERCASE';

        if (\ucfirst($str) === $str)
            return 'PascalCase';

        foreach (\str_split(\str_replace(' ', '', $str)) as $char) {
            if (\strtoupper($char) === $char)
                return 'camelCase';
        }

        return 'lowercase';
    }

    /**
     * Transforms the given string to the specified case.
     *
     * @param string $to
     * @param string $str
     * @return string
     */
    public function transformCaseTo($to, $str)
    {
        return $this->transformCaseFromTo(
            $this->identifyCase($str),
            $to,
            $str
        );
    }
}
