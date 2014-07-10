<?php

/**
 * MIT License (MIT)
 *
 * Copyright (c) 2013 Beno!t POLASZEK
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Url object representation
 * @author Beno!t POLASZEK - 2014
 */

namespace   BenTools;

Class Url {

    protected   $scheme;
    protected   $user;
    protected   $pass;
    protected   $host;
    protected   $port;
    protected   $path;
    protected   $fragment;
    protected   $query;
    protected   $params = array();

    const       DEFAULT_PORT    =   80;

    public function __construct($url = null) {

        if (is_null($url))
            $url    =   $_SERVER['REQUEST_URI'];

        # Fix for not-encoded colon characters in query string that make parse_url() return false
        $urlParts   =   explode('?', $url);

        if (array_key_exists(1, $urlParts)) :
            $urlParts[1]    =   str_replace(':', '%3A', $urlParts[1]);
            $url            =   join('?', $urlParts);
        endif;

        $data = parse_url($url);

        if (is_array($data))
            foreach ($data AS $key => $value)
                if (property_exists($this, $key))
                    $this->{$key}   =   $value;

        $this->populateParams();

    }

    /**
     * Constructor alias - useful for chaining
     */
    public static function NewInstance() {
        $CurrentClass	=	new \ReflectionClass(get_called_class());
        return $CurrentClass->NewInstanceArgs(func_get_args());
    }

    /**
     * Returns a clone of the current instance
     * @return Url
     */
    public function copy() {
        return clone $this;
    }

    /**
     * Transforms the query strings into a Params array
     * @return $this
     */
    protected function populateParams() {

        $params =   [];

        if (!empty($this->query))
            parse_str($this->query, $params);

        $this->params   =   (array) $params;
        return $this;
    }
    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Adds / edits a param into the query string
     * @param $key
     * @param null $value
     * @return $this
     */
    public function setParam($key, $value = null) {
        $this->params[$key] =   $value;
        return $this;
    }

    /**
     * Gets a parameter in the query string
     * @param $key
     * @return null
     */
    public function getParam($key) {
        return (array_key_exists($key, $this->params)) ? $this->params[$key] : null;
    }

    /**
     * Appends $path to current path - exemple /original/path becomes /original/path/with/new/path
     * @param $path
     * @return $this
     */
    public function appendToPath($path) {

        $path   =   ltrim($path, '/');

        if (strpos(strrev($this->path), '/') !== 0)
            $this->path .=  '/' . $path;
        else
            $this->path .=  $path;

        return $this;
    }

    /**
     * Drops a param from the query string
     * @param $key
     * @return $this
     */
    public function dropParam($key) {
        if (array_key_exists($key, $this->params))
            unset($this->params[$key]);
        return $this;
    }

    /**
     * Rebuilds the query string from the params array
     * @return $this
     */
    public function reBuildQuery() {
        $this->query    =   http_build_query($this->params);
        return $this;
    }

    /**
     * Rebuilds the whole url
     * @return string
     */
    public function rebuild() {

        $this->reBuildQuery();

        $url    =   '';

        if (strlen($this->scheme) > 0)
            $url .= $this->scheme . '://';

        if (strlen($this->user) > 0)
            $url .= $this->user;

        if (strlen($this->user) > 0 && strlen($this->user) > 0)
            $url .= ':';

        if (strlen($this->pass) > 0)
            $url .= $this->pass;

        if (strlen($this->user) > 0)
            $url .= '@';


        if (strlen($this->host) > 0)
            $url .= $this->host;

        if (strlen($this->port) > 0)
            $url .= ':' . $this->port;

        if (strlen($this->host) > 0 && ((strlen($this->path) > 0 && substr($this->path, 0, 1) != '/') || (strlen($this->path) == 0 && (strlen($this->query) > 0 || strlen($this->fragment) > 0))))
            $url .= '/';

        if (strlen($this->path) > 0)
            $url .= $this->path;

        if (strlen($this->query) > 0)
            $url .= '?' . $this->query;

        if (strlen($this->fragment) > 0)
            $url .= '#' . $this->fragment;

        return $url;

    }
    /**
     * @return int
     */
    public function getPort() {
        return (!$this->port) ? static::DEFAULT_PORT : $this->port;
    }

    /**
     * @return string
     */
    public function getFragment() {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPass() {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getScheme() {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Checks if the beginning of the URL is an IP address
     * @return bool
     */
    public function isIp() {
        return (bool) preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $this->host);
    }

    /**
     * String context
     * @return string
     */
    public function __toString() {
        return $this->rebuild();
    }

    /**
     * Read-only
     * @param $key
     * @param $value
     * @return bool
     */
    public function __set($key, $value) {
        return false;
    }

    /**
     * Read-only
     * @param $key
     * @return mixed
     */
    public function __get($key) {
        return $this->{$key};
    }

}