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

namespace K8s\Client\Websocket;

use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;

class ExecConnection
{
    /**
     * Generic STDIN
     */
    public const CHANNEL_STDIN = 'stdin';

    /**
     * Generic STDOUT
     */
    public const CHANNEL_STDOUT = 'stdout';

    /**
     * Generic STDERR
     */
    public const CHANNEL_STDERR = 'stderr';

    /**
     * Sub-protocol error
     */
    public const CHANNEL_ERROR = 'error';

    /**
     * Sub-protocol resize
     */
    public const CHANNEL_RESIZE = 'resize';

    /**
     * @var WebsocketConnectionInterface
     */
    private $websocket;

    public function __construct(WebsocketConnectionInterface $websocket)
    {
        $this->websocket = $websocket;
    }

    /**
     * Send a line, or multiple lines, back through STDIN. This adds on a line feed to each line.
     *
     * @param string[]|string $lines
     * @return $this
     */
    public function writeln($lines, string $lf = "\n")
    {
        $lines = array_map(function (string $line) use ($lf): string {
            return chr(0) . $line . $lf;
        }, (array)$lines);

        foreach ($lines as $line) {
            $this->websocket->send($line);
        }

        return $this;
    }

    /**
     * Send arbitrary data through STDIN.
     *
     * @param string $data
     * @return $this
     */
    public function write(string $data)
    {
        $this->websocket->send(chr(0) . $data);

        return $this;
    }

    /**
     * This sends empty data to STDIN to keep a connection open in the absence of real input.
     *
     * @return $this
     */
    public function keepalive()
    {
        $this->websocket->send(chr(0));

        return $this;
    }

    public function close(): void
    {
        $this->websocket->close();
    }
}
