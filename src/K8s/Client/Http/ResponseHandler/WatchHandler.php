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

use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\WatchEvent;
use K8s\Client\Exception\RuntimeException;
use K8s\Client\Http\HttpClient;
use JsonDecodeStream\Parser;
use Psr\Http\Message\ResponseInterface;

class WatchHandler extends AbstractHandler
{
    public function handle(ResponseInterface $response, array $options)
    {
        $watch = $this->getCallableOrFail($options);
        $stream = $response->getBody();

        $parser = Parser::fromPsr7($stream);
        foreach ($parser->items(null, true) as $item) {
            $object = $this->serializer->deserialize($item, WatchEvent::class);
            $result = call_user_func($watch, $object);
            if ($result === false) {
                break;
            }
        }
        $stream->close();

        return null;
    }

    public function supports(ResponseInterface $response, array $options): bool
    {
        $isWatch = $options['query']['watch'] ?? false;
        if (!$isWatch) {
            return false;
        }
        $this->getCallableOrFail($options);

        return $this->isResponseContentType($response, HttpClient::CONTENT_TYPE_JSON)
            && $this->isResponseSuccess($response);
    }

    private function getCallableOrFail(array $options): callable
    {
        $watch = $options['handler'] ?? null;

        if (!is_callable($watch)) {
            throw new RuntimeException(
                'When using watch in your query you must specify the "handler" callable parameter'
            );
        }

        return $watch;
    }
}
