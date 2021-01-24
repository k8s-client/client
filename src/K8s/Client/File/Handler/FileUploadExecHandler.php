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

namespace K8s\Client\File\Handler;

use K8s\Client\File\Exception\FileUploadException;
use K8s\Client\Websocket\Contract\ContainerExecInterface;
use K8s\Client\Websocket\ExecConnection;
use Psr\Http\Message\StreamInterface;

class FileUploadExecHandler implements ContainerExecInterface
{
    private const READ_BYTES = 8192;

    /**
     * @var StreamInterface
     */
    private $stream;

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function onOpen(ExecConnection $connection): void
    {
        while (!$this->stream->eof()) {
            $connection->write($this->stream->read(self::READ_BYTES));
        }
        $connection->close();
        ;
    }

    public function onClose(): void
    {
    }

    public function onReceive(string $channel, string $data, ExecConnection $connection): void
    {
        if ($channel === ExecConnection::CHANNEL_STDERR) {
            $connection->close();

            throw new FileUploadException(sprintf(
                'Error while uploading data: %s',
                $data
            ));
        }
    }
}
