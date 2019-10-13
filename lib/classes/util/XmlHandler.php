<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\FiletypeHandler;

class XmlHandler implements FiletypeHandler
{
    protected function readXml($filename)
    {
        $xml = new \DOMDocument('1.0', 'utf-8');

        $xml->loadXML(\html_entity_decode(
            (new FileHandler())->read($filename)
        ));

        return $xml;
    }

    protected function isTextNode($node)
    {
        return $node->hasChildNodes() &&
            \count($this->getChildNodes($node)) === 0 &&
            $node->childNodes->item(0)->nodeType == XML_TEXT_NODE;
    }

    protected function getTextContent($node)
    {
        return $node->childNodes->item(0)->textContent;
    }

    protected function getChildNodes($node)
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $childNodes = array();

        foreach ($node->childNodes as $childNode) {
            if (\substr($childNode->nodeName, 0, 1) !== '#') {
                $childNodes[] = $childNode;
            }
        }

        return $childNodes;
    }

    protected function getAttributes($node)
    {
        if (!$node->hasAttributes()) {
            return array();
        }

        $attributes = array();

        $domNamedNodes = $node->attributes;

        for ($i = 0; $i < $domNamedNodes->length; $i++) {
            $item = $domNamedNodes->item($i);
            $attributes[$item->nodeName] = $item->nodeValue;
        }
        unset($item);
        unset($domNamedNodes);

        return $attributes;
    }

    protected function isRootNode($node)
    {
        return $node->parentNode === null;
    }

    protected function arrayType($node)
    {
        if (
            !$node->hasChildNodes() ||
            $this->isTextNode($node)
        ) {
            return 'none';
        }

        $tagName = null;

        foreach ($this->getChildNodes($node) as $childNode) {
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
        foreach ($this->getChildNodes($xml) as $node) {
            $arr[$node->nodeName] = $this->xmlIntoArray($node);
        }

        return $arr;
    }

    protected function xmlIntoIndexArray($xml)
    {
        $arr = array();

        foreach ($this->getChildNodes($xml) as $node) {
            $arr[] = $this->xmlIntoArray($node);
        }

        return $arr;
    }

    protected function xmlIntoArray($xml)
    {
        $arr = array(
            'name' => null,
            'text' => null,
            'attributes' => array(),
            'children' => array(),
        );

        $childNodes = $this->getChildNodes($xml);

        if ($this->isRootNode($xml) && count($childNodes) > 0) {
            return $this->xmlIntoArray($childNodes[count($childNodes) - 1]);
        } else {
            $arr['name'] = $xml->nodeName;
        }

        if ($this->isTextNode($xml)) {
            $arr['text'] = $this->getTextContent($xml);
        }

        $arr['attributes'] = $this->getAttributes($xml);

        foreach ($childNodes as $node) {
            $arr['children'][] = $this->XmlIntoArray($node);
        }

        (new MemoryManager())->destroy($childNodes);
        unset($childNodes);

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
