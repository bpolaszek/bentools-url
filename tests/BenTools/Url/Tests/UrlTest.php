<?php

namespace BenTools\Url\Tests;

use BenTools\Url\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{

    function testBracketsParserAndOldParamDoesNotExistAndAppendIsFalse()
    {
        $url = Url::fromString('/?a=foo', Url::MULTIVALUED_PARAMS_WITH_BRACKETS);
        $url = $url->withParam('b', 'bar', false);
        $this->assertEquals('/?a=foo&b=bar', (string) $url);
    }

    function testBracketsParserAndOldParamDoesNotExistAndAppendIsTrue()
    {
        $url = Url::fromString('/?a=foo', Url::MULTIVALUED_PARAMS_WITH_BRACKETS);
        $url = $url->withParam('b', 'bar', true);
        $this->assertEquals('/?a=foo&b=bar', (string) $url);
    }

    function testBracketsParserAndOldParamIsScalarAndAppendIsFalse()
    {
        $url = Url::fromString('/?a=foo&b=baz', Url::MULTIVALUED_PARAMS_WITH_BRACKETS);
        $url = $url->withParam('b', 'bar', false);
        $this->assertEquals('/?a=foo&b=bar', (string) $url);
    }

    function testBracketsParserAndOldParamIsScalarAndAppendIsTrue()
    {
        $url = Url::fromString('/?a=foo&b=baz', Url::MULTIVALUED_PARAMS_WITH_BRACKETS);
        $url = $url->withParam('b', 'bar', true);
        $this->assertEquals('/?a=foo&b=bar', (string) $url);
    }

    function testBracketsParserAndOldParamIsArrayAndAppendIsFalse()
    {
        $url = Url::fromString('/?a=foo&b[]=baz', Url::MULTIVALUED_PARAMS_WITH_BRACKETS);
        $url = $url->withParam('b', 'bar', false);
        $this->assertEquals('/?a=foo&b=bar', (string) $url);
    }

    /*function testBracketsParserAndOldParamIsArrayAndAppendIsTrue()
    {
        $url = Url::fromString('/?a=foo&b[]=baz', Url::MULTIVALUED_PARAMS_WITH_BRACKETS);
        $url = $url->withParam('b', 'bar', true);
        $this->assertEquals('/?a=foo&b[]=baz&b[]=bar', (string) $url);
    }*/

    /*function testNoBracketsParserAndOldParamDoesNotExistAndAppendIsFalse()
    {
    }

    function testNoBracketsParserAndOldParamDoesNotExistAndAppendIsTrue()
    {
    }

    function testNoBracketsParserAndOldParamIsScalarAndAppendIsFalse()
    {
    }

    function testNoBracketsParserAndOldParamIsScalarAndAppendIsTrue()
    {
    }

    function testNoBracketsParserAndOldParamIsArrayAndAppendIsFalse()
    {
    }

    function testNoBracketsParserAndOldParamIsArrayAndAppendIsTrue()
    {
    }

    function testComaParserAndOldParamDoesNotExistAndAppendIsFalse()
    {
    }

    function testComaParserAndOldParamDoesNotExistAndAppendIsTrue()
    {
    }

    function testComaParserAndOldParamIsScalarAndAppendIsFalse()
    {
    }

    function testComaParserAndOldParamIsScalarAndAppendIsTrue()
    {
    }

    function testComaParserAndOldParamIsArrayAndAppendIsFalse()
    {
    }

    function testComaParserAndOldParamIsArrayAndAppendIsTrue()
    {
    }*/


}
