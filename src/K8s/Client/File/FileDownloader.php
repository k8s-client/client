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

use K8s\Client\Exception\InvalidArgumentException;
use K8s\Client\File\Contract\ArchiveInterface;
use K8s\Client\File\Exception\FileDownloadException;
use K8s\Client\File\Handler\FileDownloadExecHandler;
use K8s\Client\Kind\PodExecService;
use Throwable;

class FileDownloader
{
    use FileTrait;

    /**
     * @var PodExecService
     */
    private $exec;

    /**
     * @var ArchiveFactory
     */
    private $archiveFactory;

    /**
     * @var string|null
     */
    private $container;

    /**
     * @var string|null
     */
    private $file;

    /**
     * @var string[]
     */
    private $paths;

    /**
     * @var bool
     */
    private $compress = false;

    public function __construct(
        PodExecService $exec,
        ArchiveFactory $archiveFactory,
        ?string $container = null
    ) {
        $this->exec = $exec;
        $this->archiveFactory = $archiveFactory;
        $this->container = $container;
    }

    /**
     * Use a specific container. If not specified, defaults to the only container in the Pod.
     *
     * @param string $container the container name
     * @return $this
     */
    public function useContainer(string $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Path(s) to be download from.
     *
     * @param string|string[] $path A single path, or array of paths.
     * @return $this
     */
    public function from($path): self
    {
        if (!(is_string($path) || is_array($path))) {
            throw new InvalidArgumentException(sprintf(
                'Expected a string or array, received: %s',
                gettype($path)
            ));
        }
        $path = (array)$path;
        foreach ($path as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException(sprintf(
                    'Expected a string path, received: %s',
                    gettype($path)
                ));
            }
            $this->paths[] = $item;
        }

        return $this;
    }

    /**
     * Download to specific file. It will be created. If not provided, a file is created in a temp location.
     *
     * @param string $file a full file path.
     * @return $this
     */
    public function to(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Compress the downloaded files (depends on support by the remote system).
     *
     * @return $this
     */
    public function compress(): self
    {
        $this->compress = true;

        return $this;
    }

    /**
     * Initiate the file download process.
     *
     * @throws FileDownloadException
     */
    public function download(): ArchiveInterface
    {
        if (empty($this->paths)) {
            throw new FileDownloadException('You must provide at least one path to download.');
        }

        $suffix = $this->compress ? '.gz' : '';
        $file = new FileResource($this->file ?? $this->getTempFilename($suffix));

        if ($this->container) {
            $this->exec->useContainer($this->container);
        }

        try {
            $execHandler = new FileDownloadExecHandler($file);
            $this->exec->useStdin()
                ->useStdout()
                ->useStderr()
                ->useTty(false)
                ->command($this->getDownloadCommand())
                ->run($execHandler);

            return $this->archiveFactory->makeArchive($file->getPath());
        } catch (Throwable $e) {
            throw new FileDownloadException(
                sprintf('Failed to download files: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    private function getDownloadCommand(): array
    {
        $arg = 'c';
        if ($this->compress) {
            $arg .= 'z';
        }
        $arg .= 'f';

        $paths = [];
        foreach ($this->paths as $path) {
            $paths[] = ltrim($path, '/');
        }

        return array_merge([
            'tar',
            $arg,
            '-',
            '-C',
            '/',
        ], $paths);
    }
}
