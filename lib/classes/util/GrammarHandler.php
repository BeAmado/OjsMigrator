<?php

namespace BeAmado\OjsMigrator\Util;

class GrammarHandler
{
    /*
     * Gets the plural form of the noun.
     *
     * @param string $noun
     * @return string
     */
    protected function englishPlural($noun)
    {
        switch (\strtolower(\substr($noun, -1))) {
            case 's':
                return $noun;
            case 'y':
                return substr($noun, 0, -1) . 'ies';
        }

        return $noun . 's';
    }

    /**
     * Gets the single form of the noun.
     *
     * @param string $noun
     * @return string
     */
    protected function englishSingle($noun)
    {
        if (\substr($noun, -3) === 'ies')
            return \substr($noun, 0, -3) . 'y';

        if (
            \substr($noun, -1) === 's' && 
            !\in_array(
                \substr($noun, -2, 1),
                array('u', 's')
            )
        )
            return \substr($noun, 0, -1);

        return $noun;
    }

    /**
     * Gets the plural form of the noun in the specified language.
     * **Supported locales: en**
     *
     * @param string $str
     * @return string
     */
    public function getPlural($str, $locale = 'en')
    {
        if ($locale === 'en') {
            return $this->englishPlural($str);
        }
    }

    /**
     * Gets the singular form of the noun in the specified language.
     * **Supported locales: en**
     *
     * @param string $str
     * @return string
     */
    public function getSingle($str, $locale = 'en')
    {
        if ($locale === 'en') {
            return $this->englishSingle($str);
        }
    }
}
