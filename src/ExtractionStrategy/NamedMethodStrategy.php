<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy,
    Exception\LogicException,
};
use Innmind\Immutable\Str;

final class NamedMethodStrategy implements ExtractionStrategy
{
    public function supports(object $object, string $property): bool
    {
        $refl = new \ReflectionObject($object);

        $property = Str::of($property)
            ->camelize()
            ->toString();

        if (!$refl->hasMethod($property)) {
            return false;
        }

        $property = $refl->getMethod($property);

        if (!$property->isPublic()) {
            return false;
        }

        return $property->getNumberOfRequiredParameters() === 0;
    }

    public function extract(object $object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $property = Str::of($property)
            ->camelize()
            ->toString();

        /** @psalm-suppress MixedMethodCall */
        return $object->$property();
    }
}
