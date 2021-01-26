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

trait FileTrait
{
    private function getTempFilename(string $suffix = ''): string
    {
        return sprintf(
            '%s%s%s.tar%s',
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            uniqid('k8s-client'),
            $suffix
        );
    }
}
