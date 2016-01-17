<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategyInterface;
use Innmind\Reflection\Exception\LogicException;

class NamedMethodStrategy implements ExtractionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object, string $property): bool
    {
        $refl = new \ReflectionObject($object);

        return $refl->hasMethod($property) &&
            $refl->getMethod($property)->getNumberOfRequiredParameters() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        return $object->$property();
    }
}
