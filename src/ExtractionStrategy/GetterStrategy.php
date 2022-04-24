<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy,
    Exception\LogicException,
};
use Innmind\Immutable\Str;

final class GetterStrategy implements ExtractionStrategy
{
    private Str $getter;

    public function __construct()
    {
        $this->getter = Str::of('get%s');
    }

    public function supports(object $object, string $property): bool
    {
        $refl = new \ReflectionObject($object);
        $getter = $this
            ->getter
            ->sprintf(Str::of($property)->camelize()->ucfirst()->toString())
            ->toString();

        if (!$refl->hasMethod($getter)) {
            return false;
        }

        $getter = $refl->getMethod($getter);

        if (!$getter->isPublic()) {
            return false;
        }

        return $getter->getNumberOfRequiredParameters() === 0;
    }

    public function extract(object $object, string $property): mixed
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $getter = $this
            ->getter
            ->sprintf(Str::of($property)->camelize()->ucfirst()->toString())
            ->toString();

        /** @psalm-suppress MixedMethodCall */
        return $object->$getter();
    }
}
