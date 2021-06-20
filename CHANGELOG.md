1.7.0 (2021-06-20)
--
* Allow creating Kind models from array data using newKind().

1.6.0 (2021-03-16)
--
* Add a basic KubeConfig parser.
* Add a K8sFactory to construct the client from a KubeConfig file.
* Add HttpClientFactory and WebsocketClientFactory support for auto-configuration.

1.5.1 (2021-02-28)
--
* Add the Swoole based websocket class to the websocket factory.

1.5.0 (2021-02-21)
--
* Add the ability to proxy HTTP requests to pod, nodes, and services.
* Fix how certain error status codes are handled when received.
* Allow full access to the Response in an HttpException.

1.4.1 (2021-02-15)
--
* Fix proxy service methods by always preferring an explicit HTTP method if passed in.

1.4.0 (2021-02-14)
--
* Add the ability to port-forward from a pod.

1.3.1 (2021-02-13)
--
* Be more restrictive with the minor version of k8s/core.

1.3.0 (2021-01-31)
--
* Add the ability to perform a replace / put operation.
* Add the ability to attach the running process of a container in a Pod.
* Add the ability to read the status sub-resource.
* Add the ability to patch the status sub-resource.
* Add the ability to replace the status sub-resource.
* Minor change to allow the POST of an Eviction to work.
* Fix how empty objects and time values are serialized when sent to Kubernetes.

1.2.0 (2021-01-27)
--
* Add the ability to download files from Pods.
* Add the ability to upload files to Pods.

1.1.0 (2021-01-23)
--
* Add the ability to patch Kubernetes resources (json, strategic, merge).
* Add the ability to get the full status from a Kubernetes exception.
* Add integration tests / coverage.

1.0.0 (2021-01-14)
--
* Initial release
