<?php

namespace BenTools\Url;

use GuzzleHttp\Psr7\Uri;

class Url extends Uri
{
    /**
     * Url constructor.
     * @param null|string $uri
     */
    public function __construct($uri = null)
    {
        if (null === $uri) {
            if ('cli' === php_sapi_name()) {
                $uri = '';
            }
            else {
                $uri = $_SERVER['REQUEST_URI'];
            }
        }
        parent::__construct($uri);
    }

    /**
     * @param null $uri
     * @return Url|static
     */
    public static function create($uri = null)
    {
        return new static($uri);
    }

    /**
     * @param $key
     * @param null $value
     * @return Url|\Psr\Http\Message\UriInterface
     */
    public function withParam($key, $value = null)
    {
        return self::withQueryValue($this, $key, $value);
    }

    /**
     * @param array $params
     * @param bool $clean
     * @return Url|\Psr\Http\Message\UriInterface|static
     */
    public function withParams(array $params, $clean = false)
    {
        $url = true === $clean ? $this->withQuery('') : $this;
        foreach ($params as $key => $value) {
            $url = $url->withParam($key, $value);
        }

        return $url;
    }

    /**
     * @param $key
     * @return Url|\Psr\Http\Message\UriInterface|static
     */
    public function withoutParam($key)
    {
        return self::withoutQueryValue($this, $key);
    }

    /**
     * @param array $keys
     * @return Url|\Psr\Http\Message\UriInterface|static
     */
    public function withoutParams(array $keys)
    {
        $url = $this;
        foreach ($keys as $key) {
            $url = $this->withoutParam($key);
        }

        return $url;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $params = explode('&', $this->getQuery());
        $output = [];
        foreach ($params as $pair) {
            $pair = explode('=', $pair);
            $key = rawurldecode($pair[0]);
            $value = isset($pair[1]) ? rawurldecode($pair[1]) : null;
            $output[$key] = $value;
        }

        return $output;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getParam($key)
    {
        $params = $this->getParams();

        return array_key_exists($key, $params) ? $params[$key] : null;
    }

}