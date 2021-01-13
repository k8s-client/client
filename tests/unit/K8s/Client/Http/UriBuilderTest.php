<?php

/**
 * This file is part of the k8s/client library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace unit\K8s\Client\Http;

use K8s\Client\Http\UriBuilder;
use K8s\Client\Options;
use unit\K8s\Client\TestCase;

class UriBuilderTest extends TestCase
{
    /**
     * @var UriBuilder
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new UriBuilder(new Options('https://foo.local:8443'));
    }

    public function testItBuildsTheBasicUri(): void
    {
        $result = $this->subject->buildUri('/foo');

        $this->assertEquals('https://foo.local:8443/foo', $result);
    }

    public function testItReplacesParamsInThePath(): void
    {
        $result = $this->subject->buildUri('/foo/{name}/go', ['{name}' => 'fun']);

        $this->assertEquals('https://foo.local:8443/foo/fun/go', $result);
    }

    public function testItReplacesTheNamespaceParamInThePathWithTheOneInOptions(): void
    {
        $result = $this->subject->buildUri('/foo/{namespace}/go');

        $this->assertEquals('https://foo.local:8443/foo/default/go', $result);
    }

    public function testItReplacesTheNamespaceParamInThePathWithTheOnePassedIn(): void
    {
        $result = $this->subject->buildUri('/foo/{namespace}/go', [], [], 'stuff');

        $this->assertEquals('https://foo.local:8443/foo/stuff/go', $result);
    }

    public function testItAddsQueryParamsToTheUri(): void
    {
        $result = $this->subject->buildUri('/foo', [], ['meh' => true, 'foo' => ' bar '], 'stuff');

        $this->assertEquals('https://foo.local:8443/foo?meh=1&foo=+bar+', $result);
    }

    public function testItAddsQueryParamsThatAreArraysToTheUri(): void
    {
        $result = $this->subject->buildUri('/foo', [], ['command' => ['foo', 'bar']], 'stuff');

        $this->assertEquals('https://foo.local:8443/foo?command=foo&command=bar', $result);
    }

    public function testItAddsQueryParamsThatAreArraysAndNotArraysToTheUri(): void
    {
        $result = $this->subject->buildUri('/foo', [], ['bar' => true, 'command' => ['foo', 'bar']], 'stuff');

        $this->assertEquals('https://foo.local:8443/foo?bar=1&command=foo&command=bar', $result);
    }
}
