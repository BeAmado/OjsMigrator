<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\FiletypeHandler;

class XmlHandler implements FiletypeHandler
{
    protected function readXml($filename)
    {
        $xml = new \DOMDocument('1.0', 'utf-8');

        $xml->loadHTMLFile($filename);

        return $xml;
    }

    protected function arrayType($node)
    {
        if (!$node->hasChildNodes) {
            return 'none';
        }

        $tagName = null;

        foreach ($node->childNodes as $childNode) {
            if ($tagName === null) {
                $tagName = $childNode->nodeName;
            }

            if ($tagName !== $childNode->nodeName) {
                return 'associative';
            }
        }

        return 'indexed';
    }

    protected function xmlIntoAssocArray($xml)
    {
        $arr = array();
        foreach ($xml->childNodes as $node) {
            $arr[$node->nodeName] = $this->xmlIntoArray($node);
        }

        return $arr;
    }

    protected function xmlIntoIndexArray($xml)
    {
        $arr = array();

        foreach ($xml->childNodes as $node) {
            $arr[] = $this->xmlIntoArray($node);
        }
    }

    protected function xmlIntoArray($xml)
    {
        $arr = array();

        if (!$xml->hasChildNodes) {
            return $xml->nodeValue;
        }

        foreach ($xml->childNodes as $node) {

            switch($this->arrayType($node)) {
                case 'none':
                    $arr[$node->nodeName] = $node->nodeValue;
                    break;
                case 'associative':
                    $arr[$node->nodeName] = $this->xmlIntoAssocArray($node);
                    break;
                case 'indexed':
                    $arr[$node->nodeName] = $this->xmlIntoIndexArray($node);
                    break;
            }

        }

        return $arr;
    }

    protected function readIntoArray($filename)
    {
        return $this->xmlIntoArray($this->readXml($filename));
    }

    /**
     * Creates a MyObject object representation of the data.
     *
     * @param string $filename
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function createFromFile($filename)
    {
        return (new MemoryManager())->create($this->readIntoArray($filename));
    }

    /**
     * Dumps the object data into an xml file.
     *
     * @param string $filename
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    public function dumpToFile($filename, $obj)
    {

    }
}
