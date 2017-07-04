<?php

namespace BenTools\Url;

use function GuzzleHttp\Psr7\parse_query;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

class Url implements UriInterface
{
    const MULTIVALUED_PARAMS_WITH_BRACKETS = 1;
    const MULTIVALUED_PARAMS_WITHOUT_BRACKETS = 2;
    const MULTIVALUED_PARAMS_WITH_COMA = 3;

    /**
     * @var UriInterface
     */
    private $innerUri;

    /**
     * @var int
     */
    private $multiValuedParamsWriter;

    private static $replaceQuery = ['=' => '%3D', '&' => '%26'];

    /**
     * @var ParamsParser
     */
    private $paramsParser;

    /**
     * Url constructor.
     * @param UriInterface $innerUri
     * @param int          $multiValuedParamsWriter
     */
    public function __construct(UriInterface $innerUri, $multiValuedParamsWriter = self::MULTIVALUED_PARAMS_WITH_BRACKETS)
    {
        if (!in_array($multiValuedParamsWriter, [
            self::MULTIVALUED_PARAMS_WITH_BRACKETS,
            self::MULTIVALUED_PARAMS_WITHOUT_BRACKETS,
            self::MULTIVALUED_PARAMS_WITH_COMA,
        ], true)) {
            throw new \InvalidArgumentException("Invalid value for multiValuedParamParser");
        }
        $this->innerUri = $innerUri;
        $this->multiValuedParamsWriter = $multiValuedParamsWriter;
        $multipleKeysAsArrays = $this->multiValuedParamsWriter === self::MULTIVALUED_PARAMS_WITHOUT_BRACKETS;
        $comaValuesAsArrays = $this->multiValuedParamsWriter === self::MULTIVALUED_PARAMS_WITH_COMA;
        $this->paramsParser = new ParamsParser($multipleKeysAsArrays, $comaValuesAsArrays);
    }

    /**
     * @param null $uri
     * @return Url|static
     */
    public static function create($uri = null, $multiValuedParamParser = self::MULTIVALUED_PARAMS_WITH_BRACKETS)
    {
        return $uri instanceof UriInterface ? new static($uri, $multiValuedParamParser) : static::fromString(new Uri($uri), $multiValuedParamParser);
    }

    /**
     * @param     $string
     * @param int $multiValuedParamParser
     * @return static
     */
    public static function fromString($string, $multiValuedParamParser = self::MULTIVALUED_PARAMS_WITH_BRACKETS)
    {
        return new static(new Uri($string), $multiValuedParamParser);
    }

    /**
     * @param int $multiValuedParamParser
     * @return static
     * @throws \InvalidArgumentException
     */
    public static function fromCurrentQueryString($multiValuedParamParser = self::MULTIVALUED_PARAMS_WITH_BRACKETS)
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            throw new \InvalidArgumentException("Request Uri is empty.");
        }
        return new static(new Uri($_SERVER['QUERY_STRING']), $multiValuedParamParser);
    }

    /**
     * @param $key
     * @param null $value
     * @return Url|UriInterface
     */
    public function withParam($key, $value = null, $append = false)
    {

        $params = $this->getParams();
        if (true === $append) {
            $actualValue = isset($params[$key]) ? $params[$key] : null;
            if (null === $actualValue) {
                $params[$key] = $value;
            } elseif (!is_array($actualValue)) {
                $params[$key] = array_merge([$actualValue], $value);
            } else {
                $params[$key] = array_push($params[$key], $value);
            }
        } else {
            $params[$key] = $value;
        }
        switch ($this->multiValuedParamsWriter) {
            case self::MULTIVALUED_PARAMS_WITH_BRACKETS:
                return $this->withQuery(http_build_query($params));
        }
        throw new \RuntimeException("plop");
    }

    /**
     * @param array $params
     * @param bool $clean
     * @return Url|UriInterface
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
     * @return Url|UriInterface|static
     */
    public function withoutParam($key)
    {
        return new static(Uri::withoutQueryValue($this, $key));
    }

    /**
     * @param array $keys
     * @return Url|UriInterface|static
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
        $parse = $this->paramsParser;
        return $parse($this->getQuery());
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

    /**
     * @param $path
     * @return Url
     */
    public function withAppendedPath($path)
    {
        if (0 === mb_strlen($this->getPath())) {
            return $this->withPath($path);
        }
        $currentPath = rtrim($this->getPath(), '/');
        $newPath = sprintf('%s/%s', $currentPath, ltrim($path, '/'));
        return $this->withPath($newPath);
    }

    /**
     * @param $path
     * @return Url
     */
    public function withPrependedPath($path)
    {
        if (0 === mb_strlen($this->getPath())) {
            return $this->withPath($path);
        }
        $currentPath = ltrim($this->getPath(), '/');
        $newPath = sprintf('%s/%s', rtrim($path, '/'), $currentPath);
        return $this->withPath($newPath);
    }

    /**
     * @inheritDoc
     */
    public function getScheme()
    {
        return $this->innerUri->getScheme();
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        return $this->innerUri->getAuthority();
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        return $this->innerUri->getUserInfo();
    }

    /**
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->innerUri->getHost();
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->innerUri->getPort();
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->innerUri->getPath();
    }

    /**
     * @inheritDoc
     */
    public function getQuery()
    {
        return $this->innerUri->getQuery();
    }

    /**
     * @inheritDoc
     */
    public function getFragment()
    {
        return $this->innerUri->getFragment();
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        $cloned = clone $this;
        $cloned->innerUri = $cloned->innerUri->withScheme($scheme);
        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $cloned = clone $this;
        $cloned->innerUri = $cloned->innerUri->withUserInfo($user, $password = null);
        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        $cloned = clone $this;
        $cloned->innerUri = $cloned->innerUri->withHost($host);
        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        $cloned = clone $this;
        $cloned->innerUri = $cloned->innerUri->withPort($port);
        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        $cloned = clone $this;
        $cloned->innerUri = $cloned->innerUri->withPath($path);
        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        $cloned = clone $this;
        $cloned->innerUri = $cloned->innerUri->withQuery($query);
        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        $cloned = clone $this;
        $cloned->innerUri = $cloned->innerUri->withFragment($fragment);
        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->innerUri->__toString();
    }


}
