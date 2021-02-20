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

namespace K8s\Client\Http\Exception;

use K8s\Core\Exception\HttpException as BaseHttpException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpException extends BaseHttpException
{
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(ResponseInterface $response, Throwable $previous = null)
    {
        $this->response = $response;
        parent::__construct(
            $response->getReasonPhrase(),
            $response->getStatusCode(),
            $previous
        );
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
