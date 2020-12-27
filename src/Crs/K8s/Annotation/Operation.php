<?php

declare(strict_types=1);

namespace Crs\K8s\Annotation;

/**
 * @Annotation
 */
class Operation
{
    /**
     * @Required
     * @var string
     */
    public $type;

    /**
     * @Required
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $body;

    /**
     * @var string
     */
    public $response;
}
