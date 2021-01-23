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

namespace K8s\Client\Exception;

use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\Status;
use K8s\Core\Exception\Exception;

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
            (string)$status->getMessage(),
            (int)$status->getCode()
        );
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
