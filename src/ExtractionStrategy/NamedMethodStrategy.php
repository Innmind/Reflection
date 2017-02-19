<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\Exception\LogicException;
use Innmind\Immutable\Str;

class NamedMethodStrategy implements ExtractionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object, string $property): bool
    {
        $refl = new \ReflectionObject($object);

        $property = (string) (new Str($property))
            ->camelize()
            ->lcfirst();

        if (!$refl->hasMethod($property)) {
            return false;
        }

        $property = $refl->getMethod($property);

        if (!$property->isPublic()) {
            return false;
        }

        return $property->getNumberOfRequiredParameters() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $property = (string) (new Str($property))
            ->camelize()
            ->lcfirst();

        return $object->$property();
    }
}
