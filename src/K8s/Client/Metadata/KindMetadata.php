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

namespace K8s\Client\Metadata;

use K8s\Core\Annotation\Kind;

class KindMetadata
{
    /**
     * @var Kind
     */
    private $kind;

    public function __construct(Kind $kind)
    {
        $this->kind = $kind;
    }

    public function getKind(): string
    {
        return $this->kind->kind;
    }

    public function getVersion(): string
    {
        return $this->kind->version;
    }

    public function getGroup(): ?string
    {
        return $this->kind->group;
    }
}
