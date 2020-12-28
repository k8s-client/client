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

namespace K8s\Client\Http\ResponseHandler;

use K8s\Client\Http\Exception\HttpFollowException;
use K8s\Client\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;

class FollowHandler extends AbstractHandler
{
    /**
     * @throws HttpFollowException
     */
    public function handle(ResponseInterface $response, array $options)
    {
        $follow = $this->getCallableOrFail($options);
        $stream = $response->getBody();

        while (true) {
            try {
                $data = $stream->read(8196);
            } catch (\Throwable $exception) {
                throw new HttpFollowException(
                    sprintf(
                        'Unable to follow the HTTP stream. Make sure your HTTP client timeout / duration settings are correctly configured. Error: %s',
                        $exception->getMessage()
                    ),
                    $exception->getCode(),
                    $exception
                );
            }
            $result = call_user_func($follow, $data);

            if ($result === false) {
                $stream->close();

                return null;
            }

            if ($stream->eof()) {
                break;
            }
        }

        return null;
    }

    /**
     * @throws RuntimeException
     */
    public function supports(ResponseInterface $response, array $options): bool
    {
        $follow = $options['query']['follow'] ?? false;
        if (!$follow) {
            return false;
        }
        $this->getCallableOrFail($options);

        return $this->isResponseContentType($response, 'text/plain')
            && $this->isResponseSuccess($response);
    }

    private function getCallableOrFail(array $options): callable
    {
        $follow = $options['handler'] ?? null;

        if (!is_callable($follow)) {
            throw new RuntimeException(
                'When using follow in your query you must specify the "handler" callable parameter'
            );
        }

        return $follow;
    }
}
