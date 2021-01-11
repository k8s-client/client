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

namespace unit\K8s\Client\Metadata;

use K8s\Client\Metadata\KindMetadata;
use K8s\Core\Annotation\Kind;
use unit\K8s\Client\TestCase;

class KindMetadataTest extends TestCase
{
    /**
     * @var Kind
     */
    private $kind;

    /**
     * @var KindMetadata
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->kind = new Kind();
        $this->kind->kind = 'Pod';
        $this->kind->version = 'v1';
        $this->subject = new KindMetadata($this->kind);
    }

    public function testGetKind(): void
    {
        $this->assertEquals('Pod', $this->subject->getKind());
    }

    public function testGetVersion(): void
    {
        $this->assertEquals('v1', $this->subject->getVersion());
    }

    public function testGetGroupWhenNotSet(): void
    {
        $this->assertNull($this->subject->getGroup());
    }

    public function testGetGroup(): void
    {
        $this->kind->group = 'app';

        $this->assertEquals('app', $this->subject->getGroup());
    }
}
