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

namespace unit\K8s\Client\Patch;

use K8s\Client\Patch\MergePatch;
use unit\K8s\Client\TestCase;

class MergePatchTest extends TestCase
{
    /**
     * @var MergePatch
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new MergePatch(['foo' => 'bar']);
    }

    public function testItReturnsDataForToArray(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->subject->toArray());
    }

    public function testItHasTheCorrectContentType(): void
    {
        $this->assertEquals('application/merge-patch+json', $this->subject->getContentType());
    }
}
