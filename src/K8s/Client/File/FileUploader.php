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

use K8s\Client\File\Contract\ArchiveInterface;
use K8s\Client\File\Contract\ArchiveUploadInterface;
use K8s\Client\File\Exception\FileUploadException;
use K8s\Client\File\Handler\FileUploadExecHandler;
use K8s\Client\Kind\PodExecService;
use Throwable;

class FileUploader
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
     * @var ArchiveFactory
     */
    private $archiveFactory;

    /**
     * @var ArchiveInterface|null
     */
    private $archive = null;

    public function __construct(
        ArchiveFactory $archiveFactory,
        PodExecService $exec,
        ?string $container = null
    ) {
        $this->archiveFactory = $archiveFactory;
        $this->exec = $exec;
        $this->container = $container;
    }

    /**
     * Add a file to be uploaded.
     *
     * @param string $source The path to a local file.
     * @param string $destination The path the file should end up at on the container.
     * @return $this
     * @throws FileUploadException
     */
    public function addFile(string $source, string $destination): self
    {
        $this->archive()->addFile($source, $destination);

        return $this;
    }

    /**
     * Add a file by using string data.
     *
     * @param string $destination The path the file should end up at on the container.
     * @param string $data The data for the file.
     * @return $this
     * @throws FileUploadException
     */
    public function addFileFromString(string $destination, string $data): self
    {
        $this->archive()->addFromString($destination, $data);

        return $this;
    }

    /**
     * Upload to a specific container. If not set, defaults to the only container on the pod.
     *
     * @param string $container The container name to upload to.
     * @return $this
     */
    public function useContainer(string $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Uploads the specified file(s) to the Pod's container.
     *
     * @param ArchiveUploadInterface|null $archive You can optionally provide your own class implementing ArchiveUploadInterface.
     *                                             The Stream provided by this class is fed to the input of the upload command.
     * @throws FileUploadException
     */
    public function upload(?ArchiveUploadInterface $archive = null): void
    {
        if ($archive === null && $this->archive === null) {
            throw new FileUploadException('You must provide files / data to upload, or your own TAR stream.');
        }
        $archive = $archive ?? $this->archive;

        if ($this->container) {
            $this->exec->useContainer($this->container);
        }

        try {
            # This solution requires tar, but this is also how kubectl works.
            $execHandler = new FileUploadExecHandler($archive->toStream());
            $this->exec->useStdin()
                ->useStdout()
                ->useStderr()
                ->useTty(false)
                ->command($archive->getUploadCommand())
                ->run($execHandler);
        } catch (Throwable $e) {
            throw new FileUploadException(
                sprintf('Failed to upload file: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        } finally {
            # In this case we only delete the archive if it was created by this class...
            if ($this->archive) {
                $this->archive->delete();
                $this->archive = null;
            }
        }
    }

    /**
     * @throws FileUploadException
     */
    private function archive(): ArchiveInterface
    {
        if ($this->archive) {
            return $this->archive;
        }
        try {
            $this->archive = $this->archiveFactory->makeArchive($this->getTempFilename());
        } catch (Throwable $e) {
            throw new FileUploadException(
                sprintf('Failed to create tar archive for upload: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $this->archive;
    }
}
