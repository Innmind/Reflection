<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Visitor;

use Innmind\Reflection\Exception\{
    PropertyNotFoundException,
    InvalidArgumentException
};

final class AccessProperty
{
    /**
     * @throws PropertyNotFoundException
     */
    public function __invoke(object $object, string $property): \ReflectionProperty
    {
        try {
            return $this->byObject($object, $property);
        } catch (PropertyNotFoundException $e) {
            return $this->byClass(get_class($object), $property);
        }
    }

    private function byObject($object, string $property): \ReflectionProperty
    {
        $refl = new \ReflectionObject($object);

        if ($refl->hasProperty($property)) {
            return $refl->getProperty($property);
        }

        throw new PropertyNotFoundException;
    }

    private function byClass(string $class, string $property): \ReflectionProperty
    {
        $refl = new \ReflectionClass($class);

        if ($refl->hasProperty($property)) {
            return $refl->getProperty($property);
        }

        if ($refl->getParentClass()) {
            return $this->byClass(
                $refl->getParentClass()->getName(),
                $property
            );
        }

        throw new PropertyNotFoundException;
    }
}
