<?php

/**
 * This file is part of the crs/k8s library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crs\K8s\Exception;

use Crs\K8s\Model\ApiMachinery\Apis\Meta\v1\Status;

class KubernetesException extends Exception
{
    /**
     * @var Status
     */
    private $status;

    public function __construct(Status $status)
    {
        $this->status = $status;
        parent::__construct(
            $status->getMessage(),
            $status->getCode()
        );
    }
}
