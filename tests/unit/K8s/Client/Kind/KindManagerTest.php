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

namespace unit\K8s\Client\Kind;

use Http\Discovery\Psr17FactoryDiscovery;
use K8s\Api\Model\Api\AdmissionRegistration\v1\MutatingWebhookConfiguration;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\Status;
use K8s\Client\Http\HttpClient;
use K8s\Client\Http\UriBuilder;
use K8s\Client\Kind\KindManager;
use K8s\Client\Metadata\MetadataCache;
use K8s\Client\Metadata\ModelMetadata;
use K8s\Client\Metadata\OperationMetadata;
use K8s\Client\Options;
use K8s\Client\Patch\JsonPatch;
use Psr\Http\Message\ResponseInterface;
use unit\K8s\Client\TestCase;

class KindManagerTest extends TestCase
{
    /**
     * @var MetadataCache|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $metadataCache;

    /**
     * @var UriBuilder
     */
    private $urlBuilder;

    /**
     * @var HttpClient|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $httpClient;

    /**
     * @var Options
     */
    private $options;

    /**
     * @var KindManager
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->httpClient = \Mockery::spy(HttpClient::class);
        $this->options = new Options('https://foo.local');
        $this->urlBuilder = new UriBuilder($this->options);
        $this->metadataCache = \Mockery::spy(MetadataCache::class);
        $this->subject = new KindManager(
            $this->httpClient,
            $this->urlBuilder,
            $this->metadataCache,
            $this->options
        );
    }

    public function testCreate(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('post')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => true,
        ]);

        $newPod = new Pod('foo', []);
        $this->httpClient->shouldReceive('send')
            ->andReturn($newPod);

        $result = $this->subject->create(new Pod('foo', []));
        $this->assertEquals($newPod, $result);
    }

    public function testDelete(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('delete')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $deletedPod = new Pod('foo', []);
        $this->httpClient->shouldReceive('send')
            ->andReturn($deletedPod);

        $result = $this->subject->delete(new Pod('foo', []));
        $this->assertEquals($deletedPod, $result);
    }

    public function testDeleteAllNamespaced(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('deletecollection')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $status = \Mockery::spy(Status::class);
        $this->httpClient->shouldReceive('send')
            ->andReturn($status);

        $result = $this->subject->deleteAllNamespaced(Pod::class);
        $this->assertEquals($status, $result);
    }

    public function testDeleteAll(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(MutatingWebhookConfiguration::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('deletecollection-all')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $status = \Mockery::spy(Status::class);
        $this->httpClient->shouldReceive('send')
            ->andReturn($status);

        $result = $this->subject->deleteAll(MutatingWebhookConfiguration::class);
        $this->assertEquals($status, $result);
    }

    public function testRead(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(MutatingWebhookConfiguration::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('get')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $status = \Mockery::spy(Status::class);
        $this->httpClient->shouldReceive('send')
            ->andReturn($status);

        $result = $this->subject->read('foo', MutatingWebhookConfiguration::class);
        $this->assertEquals($status, $result);
    }

    public function testListAll(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(MutatingWebhookConfiguration::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('list-all')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $this->httpClient->shouldReceive('send')
            ->andReturn([]);

        $result = $this->subject->listAll(MutatingWebhookConfiguration::class);
        $this->assertEquals([], $result);
    }

    public function testListNamespaced(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('list')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $this->httpClient->shouldReceive('send')
            ->andReturn([]);

        $result = $this->subject->listNamespaced(Pod::class);
        $this->assertEquals([], $result);
    }

    public function testWatchAll(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('watch-all')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $this->httpClient->shouldReceive('send')
            ->andReturn([]);

        $this->subject->watchAll(function(){}, Pod::class);
    }

    public function testWatchNamespaced(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('watch')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $this->httpClient->shouldReceive('send')
            ->andReturn([]);

        $this->subject->watchNamespaced(function(){}, Pod::class);
    }

    public function testReplace(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('put')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => true,
        ]);

        $pod = new Pod('foo', []);
        $this->httpClient->shouldReceive('send')
            ->andReturn($pod);

        $result = $this->subject->replace($pod);
        $this->assertEquals($pod, $result);
    }

    public function testProxy(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('proxy')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $response = \Mockery::spy(ResponseInterface::class);
        $this->httpClient->shouldReceive('send')
            ->andReturn($response);

        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $request = $requestFactory->createRequest('GET', '/');

        $pod = new Pod('foo', []);
        $result = $this->subject->proxy($pod, $request);
        $this->assertEquals($response, $result);
    }

    public function testReplaceStatus(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('put-status')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => true,
        ]);

        $pod = new Pod('foo', []);
        $this->httpClient->shouldReceive('send')
            ->andReturn($pod);

        $result = $this->subject->replaceStatus($pod);
        $this->assertEquals($pod, $result);
    }

    public function testReadStatus(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('get-status')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $status = \Mockery::spy(Status::class);
        $this->httpClient->shouldReceive('send')
            ->andReturn($status);

        $result = $this->subject->readStatus('foo', Pod::class);
        $this->assertEquals($status, $result);
    }

    public function testPatchStatus(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('patch-status')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $status = \Mockery::spy(Status::class);
        $this->httpClient->shouldReceive('send')
            ->andReturn($status);

        $result = $this->subject->patchStatus(new Pod('foo', []), new JsonPatch());
        $this->assertEquals($status, $result);
    }

    public function testPatch(): void
    {
        $metadata = \Mockery::spy(ModelMetadata::class);
        $this->metadataCache->shouldReceive('get')
            ->with(Pod::class)
            ->andReturn($metadata);

        $operation = \Mockery::spy(OperationMetadata::class);
        $metadata->shouldReceive('getOperationByType')
            ->with('patch')
            ->andReturn($operation);

        $operation->shouldReceive([
            'isBodyRequired' => false,
        ]);

        $status = \Mockery::spy(Status::class);
        $this->httpClient->shouldReceive('send')
            ->andReturn($status);

        $result = $this->subject->patch(new Pod('foo', []), new JsonPatch());
        $this->assertEquals($status, $result);
    }
}
