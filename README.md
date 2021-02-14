# k8s-client ![](https://github.com/ChadSikorra/k8s-client/workflows/Build/badge.svg) [![codecov](https://codecov.io/gh/ChadSikorra/k8s-client/branch/master/graph/badge.svg)](https://codecov.io/gh/ChadSikorra/k8s-client)

k8s-client is a Kubernetes API client for PHP.

* HTTP Client agnostic (supports any [PSR-18 compatible HTTP Client](https://packagist.org/providers/psr/http-client-implementation))
* Supports all major API operations (read, watch, list, patch, delete, exec, attach, logs, port-forward, etc)
* Supports all Kinds from the Kubernetes API (via auto-generated Kind models with annotations and type-hints).
* Pluggable websocket adapter support (For executing commands in pods, attaching, port-forwarding, etc)

The Kind models are auto-generated nightly for the last 10 versions of the Kubernetes API.

* [Installation](#installation)
    * [Using a Specific Kubernetes Version](#using-a-specific-kubernetes-api-version)
    * [Installing a Websocket Adapter](#installing-a-websocket-adapter)
    * [Constructing the Client](#constructing-the-client)
* [Examples](#examples)
    * [List All Pods](#list-all-pods)
    * [Watch All Deployments in a Namespace](#watch-all-deployments-in-a-namespace)
    * [Create a Pod](#create-a-pod)
    * [Create a Deployment](#create-a-deployment)
    * [Patch a Deployment](#patch-a-deployment)
    * [Get Logs for a Pod](#get-logs-for-a-pod)
    * [Follow Logs for a Pod](#follow-logs-for-a-pod)
    * [Execute a Command in a Pod](#execute-a-command-in-a-pod-container)
    * [Attach to the Running Process of a Pod](#attach-to-the-running-process-of-a-container-in-a-pod)
    * [Download Files form a Pod](#download-files-from-a-pod)
    * [Upload Files to a Pod](#upload-files-to-a-pod)
    * [Port Forwarding from a Pod](#port-forwarding-from-a-pod)

## Installation

Install using composer:

`composer require k8s/client`

This library requires a [PSR-18 compatible HTTP Client](https://packagist.org/providers/psr/http-client-implementation), such as Guzzle or Symfony's HttpClient.
It can also be given a [PSR-16 compatible Simple Cache implementation](https://packagist.org/providers/psr/simple-cache-implementation) to help speed up the library.

### Using a Specific Kubernetes API version

Each Kubernetes version may have different resources and operations. If you require a specific version, then you can
require the version of the `k8s/api` library that you need to use. That library contains all the API specific versions
and models that are consumed by this library.

For instance, to use API version 1.18:

`composer require k8s/api:"~1.18.0"`

**Note**: The version of `k8s/api` does not exactly reflect the version of the Kubernetes API. The patch version of 
Kubernetes may not be the same as the `k8s/api` patch version.

### Installing a Websocket Adapter

Certain Kuberenetes API endpoints (such as exec, to run commands in a container) require websockets to communicate. If you
need support for this, install this adapter:

`composer require k8s/ws-ratchet`

See that library's readme for more information: https://github.com/ChadSikorra/k8s-ws-ratchet

### Constructing the Client

Construct the client with your needed options:

```php
use K8s\Client\K8s;
use K8s\Client\Options;

# Supply the base path to the Kubernetes API endpoint:
$options = new Options('https://127.0.0.1:8443');
# To use an API token for authentication, set it in the options:
$options->setToken('some-secret-token-value-goes-here');

$k8s = new K8s($options);
```

**Note**: If you need to perform certificate based authentication, check the options for the HttpClient you are using.

##Examples

### List all Pods

```php
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

/** @var Pod $pod */
foreach ($k8s->listAll(Pod::class) as $pod) {
    echo sprintf(
        "%s\t%s\t%s",
        $pod->getPodIP(),
        $pod->getNamespace(),
        $pod->getName()
    ) . PHP_EOL;
}
```

### Watch all Deployments in a Namespace

```php
use K8s\Api\Model\Api\Apps\v1\Deployment;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\WatchEvent;
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

$count = 0;

# This will watch all deployments in the default namespace.
# Change the namespace either in the Options above or as a parameter to the watchNamespaced method below.
$k8s->watchNamespaced(function (WatchEvent $event) use (&$count) {
    $count++;

    /** @var Deployment $object */
    $object = $event->getObject();
    echo sprintf(
            "%s\t%s\t%s\t%s",
            $event->getType(),
            $object->getName(),
            $object->getReplicas(),
            implode(',', (array)$object->getLabels())
        ) . PHP_EOL;
    
    # Return false if some condition is met to stop watching.
    if ($count >= 5) {
        return false;
    }
}, Deployment::class);
```

### Create a Pod

```php
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

# Create a pod with the name "web" using the nginx:latest image...
$pod = new Pod(
    'web',
    [new Container('web', 'nginx:latest')]
);

# Create will return the updated Pod object after creation in this instance...
$pod = $k8s->create($pod);

var_dump($pod);
```

### Create a Deployment

```php
use K8s\Api\Model\Api\Apps\v1\Deployment;
use K8s\Api\Model\ApiMachinery\Apis\Meta\v1\LabelSelector;
use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\PodTemplateSpec;
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

# All deployments need a "template" that describes the Pod spec
$template = new PodTemplateSpec(
    'frontend',
    [new Container('frontend', 'nginx:latest')]
);

# The template must have a label that matches the label selector below
$template->setLabels(['app' => 'web']);

# Create a deployment called "frontend" with the given template.
$deployment = new Deployment(
    'frontend',
    new LabelSelector([], ['app' => 'web']),
    $template
);

$result = $k8s->create($deployment);

# Create for a deployment will return a Status object for the creation
var_dump($result);
```

### Get Logs for a Pod

```php
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

# Read logs from a pod called "web".
# Also append all log entries with a timestamp (ISO8601)
$log = $k8s->logs('web')
    ->withTimestamps()
    ->read();

var_dump($log);
```

### Follow Logs for a Pod

```php
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

$count = 0;

# Follow logs from a pod called "web".
# Also append all log entries with a timestamp (ISO8601)
$k8s->logs('web')
    ->withTimestamps()
    ->follow(function (string $log) use (&$count) {
        $count++;
        var_dump($log);

        # Return false at any point to stop following the logs.
        if ($count >= 5) {
            return false;
        }
    });
```

### Execute a command in a Pod container

```php
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

# Print the result of "whoami".
$k8s->exec('web', '/usr/bin/whoami')
    ->useStdout()
    ->run(function (string $channel, string $data) {
        echo sprintf(
            '%s => %s',
            $channel,
            $data
        ) . PHP_EOL;
    });
```

### Attach to the running process of a container in a Pod

```php
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

# Attaches to the main running process of the container in the Pod
$k8s->attach('my-pod')
    # You must specify at least one of useStdout(), useStderr(), useStdin()
    ->useStdout()
    # Prints out any STDOUT from the main running process
    # Can also pass it an instance of ContainerExecInterface
    ->run(function (string $channel, string $data) {
        echo sprintf(
            "%s => %s",
            $channel,
            $data
        ) . PHP_EOL;
    });
```

### Patch a Deployment

```php
use K8s\Api\Model\Api\Apps\v1\Deployment;
use K8s\Client\K8s;
use K8s\Client\Patch\JsonPatch;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

$patch = new JsonPatch();
# Since labels are an array, this actually replaces existing labels
$patch->add('/metadata/labels', ['app' => 'web']);
# Replaces the current replica value with 2
$patch->replace('/spec/replicas', 2);

# We first need to read the deployment we want to patch.
$deployment = $k8s->read('frontend', Deployment::class);
# Now we patch the deployment using the patch object. The returned value will be the updated deployment.
$deployment = $k8s->patch($deployment, $patch);

echo sprintf(
    'Replicas: %s, Labels: %s',
    $deployment->getReplicas(),
    implode(',', $deployment->getLabels())
) . PHP_EOL;
```

### Upload Files to a Pod

```php
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

$k8s->uploader('my-pod')
    # Add files from paths.
    # The first argument is the source location, the second is the destination for it on the container.
    ->addFile('/path/to/local/file.txt', '/tmp/file.txt')
    # Add files from string data.
    # The first argument is the destination path on the container. The second is the file contents as a string.
    ->addFileFromString('/tmp/hi.txt', 'Oh, hi Mark.')
    # This actually initiates the upload process.
    ->upload();
```

### Download Files from a Pod

```php
use K8s\Client\K8s;
use K8s\Client\Options;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

$archive = $k8s->downloader('my-pod')
    # Optionally choose to compress the downloaded files (gzip -- tar.gz)
    ->compress()
    # The file(s) or directory to download. Can be an array of files, or just a single directory or file.
    ->from('/etc')
    # If you don't specify to() it will download to a temp file.
    ->to(__DIR__ . '/' . 'podFiles.tar.gz')
    # Initiate the download process.
    ->download();

# The full path to the downloaded files archive..
echo (string)$archive . PHP_EOL;
# Extract the downloaded files to a directory called "podFiles" in the current directory..
mkdir(__DIR__ . '/podFiles');
$archive->extractTo(__DIR__ . '/podFiles');
```

### Port Forwarding from a Pod

**Note**: The below example assumes a pod called `portforward-example` exists with port 80 serving HTTP (such as a base nginx image).

Create a class that reacts to port forwarding events:

```php
namespace App;

use K8s\Client\Websocket\Contract\PortChannelInterface;
use K8s\Client\Websocket\Contract\PortForwardInterface;
use K8s\Client\Websocket\PortChannels;

class PortForwarder implements PortForwardInterface
{
    /**
    * @var PortChannels
    */
    private $portChannels;

    /**
    * @inheritDoc
    */
    public function onInitialize(PortChannels $portChannels) : void
    {
        $this->portChannels = $portChannels;

        # On initialize, send this HTTP request across.
        # Due to "Connection: close" HTTP instruction, the websocket will close after the response is received.
        # In a more realistic situation, you'd probably want to keep this open, and react in the onDataReceived method.
        $data = "GET / HTTP/1.1\r\n";
        $data .= "Host: 127.0.0.1\r\n";
        $data .= "Connection: close\r\n";
        $data .= "Accept: */*\r\n";
        $data .= "\r\n";

        $this->portChannels->writeToPort(80, $data);
    }

    /**
    * @inheritDoc
    */
    public function onDataReceived(string $data, PortChannelInterface $portChannel) : void
    {
        echo sprintf(
            'Received data on port %s:',
            $portChannel->getPortNumber()
        ) . PHP_EOL;
        echo $data . PHP_EOL;
    }

    /**
    * @inheritDoc
    */
    public function onErrorReceived(string $data, PortChannelInterface $portChannel) : void
    {
        echo sprintf(
            'Received error on port %s: %s',
            $portChannel->getPortNumber(),
            $data
        ) . PHP_EOL;
    }
    
    /**
    * @inheritDoc
    */
    public function onClose() : void
    {
        # Do something here to clean-up resources when the connection is closed...
    }
}
```

Use the above class as a handler for the port forward process:

```php
use K8s\Client\K8s;
use K8s\Client\Options;
use App\PortForwarder;

$k8s = new K8s(new Options('https://127.0.0.1:8443'));

$handler = new PortForwarder();
# Assuming a Pod with a basic HTTP port 80 exposed...
$k8s->portforward('portforward-example', 80)
    ->start($handler);
```
