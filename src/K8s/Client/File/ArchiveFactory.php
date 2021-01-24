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

namespace K8s\Client\File;

use K8s\Client\File\Archive\Tar;
use K8s\Client\File\Contract\ArchiveInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ArchiveFactory
{
    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    public function makeArchive(string $file): ArchiveInterface
    {
        return new Tar(
            $file,
            $this->streamFactory
        );
    }
}
