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

namespace K8s\Client\Metadata;

use K8s\Core\Annotation\Attribute;
use K8s\Core\Annotation\Kind;
use K8s\Core\Annotation\Operation;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionProperty;

class MetadataParser
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    public function __construct(?AnnotationReader $annotationReader = null)
    {
        $this->annotationReader = $annotationReader ?? new AnnotationReader();
    }

    /**
     * @param class-string $modelFqcn
     */
    public function parse(string $modelFqcn): ModelMetadata
    {
        $modelClass = new ReflectionClass($modelFqcn);
        $metadata = $this->parseModelMetadata($modelClass);

        /** @var ModelMetadata[] $parents */
        $parents = [];
        while (($modelClass = $modelClass->getParentClass()) !== false) {
            $parents[] = $this->parseModelMetadata($modelClass);
        }

        if (empty($parents)) {
            return $metadata;
        }

        $kind = $metadata->getKind();
        $operations = $metadata->getOperations();
        $properties = $metadata->getProperties();

        foreach ($parents as $parent) {
            $operations = array_merge($operations, $parent->getOperations());
            $properties = array_merge($properties, $metadata->getProperties());
            if (!$kind && $parent->getKind()) {
                $kind = $parent->getKind();
            }
        }

        return new ModelMetadata(
            $modelFqcn,
            $properties,
            $operations,
            $kind
        );
    }

    private function parseModelMetadata(ReflectionClass $modelClass): ModelMetadata
    {
        $kind = null;
        $operations = [];
        $properties = [];

        $classAnnotations = $this->annotationReader->getClassAnnotations($modelClass);

        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation instanceof Kind) {
                $kind = new KindMetadata($classAnnotation);
            } elseif ($classAnnotation instanceof Operation) {
                $operations[] = new OperationMetadata($classAnnotation);
            }
        }

        foreach ($modelClass->getProperties() as $modelProperty) {
            $annotations = $this->annotationReader->getPropertyAnnotations($modelProperty);
            $metadata = $this->getPropertyMetadata($annotations, $modelProperty);
            if ($metadata) {
                $properties[] = $metadata;
            }
        }

        return new ModelMetadata(
            $modelClass->getName(),
            $properties,
            $operations,
            $kind
        );
    }

    private function getPropertyMetadata(array $annotations, ReflectionProperty $modelProperty): ?ModelPropertyMetadata
    {
        $modelPropertyMetadata = null;

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Attribute) {
                $modelPropertyMetadata = new ModelPropertyMetadata(
                    $modelProperty->getName(),
                    $annotation
                );
                break;
            }
        }

        return $modelPropertyMetadata;
    }
}
