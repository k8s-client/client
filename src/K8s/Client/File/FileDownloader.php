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
use K8s\Client\File\Exception\FileDownloadException;
use K8s\Client\File\Handler\FileDownloadExecHandler;
use K8s\Client\Kind\PodExecService;
use Phar;
use PharData;
use Throwable;

class FileDownloader
{
    use FileTrait;

    /**
     * @var PodExecService
     */
    private $exec;

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
        ?string $container = null
    ) {
        $this->exec = $exec;
        $this->container = $container;
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
    public function toFile(string $file): self
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
    public function download(): PharData
    {
        if (empty($this->paths)) {
            throw new FileDownloadException('You must provide at least one path to download.');
        }

        $suffix = $this->compress ? '.gz' : '';
        $file = new FileResource($this->file ?? $this->getTempFilename($suffix));

        try {
            $execHandler = new FileDownloadExecHandler($file);
            $this->exec->useStdin()
                ->useStdout()
                ->useStderr()
                ->useTty(false)
                ->command($this->getDownloadCommand())
                ->run(
                    $execHandler,
                    $this->container
                );
            $format = $this->compress ? Phar::GZ : Phar::TAR;

            return new PharData(
                $file->getPath(),
                0,
                '',
                $format
            );
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
            $paths[] = ltrim($path,'/');
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
