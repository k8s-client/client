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

namespace K8s\Client\File\Archive;

use K8s\Client\File\Contract\ArchiveInterface;
use K8s\Client\File\Exception\FileException;
use PharData;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class Tar implements ArchiveInterface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var PharData
     */
    private $tar;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(string $file, StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
        $this->file = $file;
        $this->tar = new PharData($file);
    }

    public function addFile(string $source, string $destination): void
    {
        $this->tar->addFile($source, $destination);
    }

    public function addFromString(string $destination, string $data): void
    {
        $this->tar->addFromString($destination, $data);
    }

    public function toStream(): StreamInterface
    {
        if (!file_exists($this->file)) {
            throw new FileException(sprintf(
                'The archive was not found. You must add files / data for it to be created.'
            ));
        }

        return $this->streamFactory->createStreamFromFile($this->file);
    }

    public function delete(): void
    {
        if (!file_exists($this->file)) {
            return;
        }
        if (!unlink($this->file)) {
            throw new FileException(sprintf(
                'Unable to remove tar archive: %s',
                $this->file
            ));
        }
    }

    public function getUploadCommand(): array
    {
        return [
            'tar',
            'xf',
            '-',
            '-C',
            '/',
        ];
    }
}
