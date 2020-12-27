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

namespace Crs\K8s\Websocket;

class Frame
{
    /**
     * @var int
     */
    private $opcode;

    /**
     * @var int
     */
    private $payloadLength;

    /**
     * @var string
     */
    private $payload;

    public function __construct(int $opcode, int $payloadLength, string $payload)
    {
        $this->opcode = $opcode;
        $this->payloadLength = $payloadLength;
        $this->payload = $payload;
    }

    public function getOpcode(): int
    {
        return $this->opcode;
    }

    public function getPayloadLength(): int
    {
        return $this->payloadLength;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }
}
