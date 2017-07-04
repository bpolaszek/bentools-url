<?php

namespace BenTools\Url;

class ParamsParser
{
    /**
     * @var bool
     */
    private $multipleKeysAsArrays = false;

    /**
     * @var bool
     */
    private $comaValuesAsArrays = false;

    /**
     * ParamsParser constructor.
     * @param bool   $multipleKeysAsArrays
     * @param bool   $comaValuesAsArrays
     */
    public function __construct(bool $multipleKeysAsArrays = false, bool $comaValuesAsArrays = false)
    {
        $this->multipleKeysAsArrays = $multipleKeysAsArrays;
        $this->comaValuesAsArrays = $comaValuesAsArrays;
    }

    /**
     * @param string $query
     * @return array
     */
    private function getPairs(string $query): array
    {
        return array_map(function ($pair) {
            $pair = explode('=', $pair);
            $pair[0] = rawurldecode($pair[0]);
            if (1 === count($pair)) {
                $pair[1] = null;
            }
            return $pair;
        }, explode('&', $query));
    }

    /**
     * @param $key
     * @return string
     */
    private function getRootKey($key)
    {
        return strstr($key, '[', true) ?: $key;
    }

    /**
     * @param $key
     * @return array
     */
    private function getPath($key): array
    {
        return array_map(function($node){
            return '' === $node ? null : $node;
        }, preg_match_all('/\[(.*?)\]/', $key, $matches) ? $matches[1] : []);
    }

    /**
     * @param array $params
     * @param       $root
     * @param array $path
     * @param       $value
     */
    private function hydrate(array &$params, $root, array $path, $value)
    {
        if (true === $this->comaValuesAsArrays && false !== strpos($value, ',')) {
            $value = explode(',', $value);
        }
        array_unshift($path, $root);
        $currentNode = &$params;
        foreach ($path as $node) {
            if (!is_array($currentNode)) {
                $currentNode = [];
            }
            if (null === $node) {
                $currentNode = &$currentNode[];
            }
            else {
                $currentNode = &$currentNode[$node];
            }
        }
        if (true === $this->multipleKeysAsArrays) {
            if (!empty($currentNode)) {
                if (!is_array($currentNode)) {
                    $currentNode = [$currentNode];
                }
                $currentNode = array_merge($currentNode, is_array($value) ? $value : [$value]);
            } else {
                $currentNode = $value;
            }
        }
        else {
            $currentNode = $value;
        }
    }

    /**
     * @param string $query
     * @return array
     */
    public function __invoke(string $query): array
    {
        $params = [];
        foreach ($this->getPairs($query) as $pair) {
            list($key, $value) = $pair;
            $root = $this->getRootKey($key);
            $path = $this->getPath($key);
            $this->hydrate($params, $root, $path, $value);
        }
        return $params;
    }

}