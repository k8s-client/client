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

namespace Crs\K8s\Websocket\Contract;

use Crs\K8s\Websocket\Exception\WebsocketException;
use Psr\Http\Message\RequestInterface;

interface WebsocketClientInterface
{
    /**
     * @throws WebsocketException
     */
    public function connect(string $subprotocol, RequestInterface $request, FrameHandlerInterface $payloadHandler): void;
}
