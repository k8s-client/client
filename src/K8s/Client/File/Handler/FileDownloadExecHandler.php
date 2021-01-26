<?php

declare(strict_types=1);

namespace K8s\Client\File\Handler;

use K8s\Client\File\Exception\FileDownloadException;
use K8s\Client\File\FileResource;
use K8s\Client\Websocket\Contract\ContainerExecInterface;
use K8s\Client\Websocket\ExecConnection;

class FileDownloadExecHandler implements ContainerExecInterface
{
    /**
     * @var FileResource
     */
    private $file;

    public function __construct(FileResource $file)
    {
        $this->file = $file;
    }

    public function onOpen(ExecConnection $connection): void
    {
    }

    public function onClose(): void
    {
        $this->file->close();
    }

    public function onReceive(string $channel, string $data, ExecConnection $connection): void
    {
        if ($channel === ExecConnection::CHANNEL_STDERR) {
            throw new FileDownloadException(sprintf(
                'Unable to download files from Pod: %s',
                $data
            ));
        }
        if ($channel !== ExecConnection::CHANNEL_STDOUT) {
            return;
        }
        $this->file->write($data);
    }
}
