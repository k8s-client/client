# k8s-client

k8s-client is a Kubernetes API client for PHP. The Kubernetes resource models and services used by this library are auto-generated
from the OpenAPI spec of the Kubernetes API. This library provides an easy way of using those models / services from different
API versions.

## Installation

Install using composer:

`composer require k8s/client`

This library requires a [PSR-18 compatible HTTP Client](https://packagist.org/providers/psr/http-client-implementation), such as Guzzle or Symfony's HttpClient.
It can also be given a [PSR-16 compatible Simple Cache implementation](https://packagist.org/providers/psr/simple-cache-implementation) to help speed up the library.

## Using a Specific Kubernetes API version

Each Kubernetes version may have different resources and operations. If you require a specific version, then you can
require the version of the `k8s/api` library that you need to use. That library contains all the API specific versions
and models that are consumed by this library.

For instance, to use API version 1.18:

`composer require k8s/api:"~1.18.0"`

**Note**: The version of `k8s/api` does not exactly reflect the version of the Kubernetes API. The patch version of 
Kubernetes may not be the same as the `k8s/api` patch version.

## Installing a Websocket Adapter

Certain Kuberenetes API endpoints (such as exec, to run commands in a container) require websockets to communicate. If you
need support for this, install this adapter:

`composer require k8s/ws-ratchet`

See that library's readme for more information: https://github.com/ChadSikorra/k8s-ws-ratchet

## Using the Client

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
