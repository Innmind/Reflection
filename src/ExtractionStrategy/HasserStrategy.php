<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy,
    Exception\LogicException,
};
use Innmind\Immutable\Str;

final class HasserStrategy implements ExtractionStrategy
{
    private Str $hasser;

    public function __construct()
    {
        $this->hasser = Str::of('has%s');
    }

    public function supports(object $object, string $property): bool
    {
        $refl = new \ReflectionObject($object);
        $hasser = $this
            ->hasser
            ->sprintf(Str::of($property)->camelize()->ucfirst()->toString())
            ->toString();

        if (!$refl->hasMethod($hasser)) {
            return false;
        }

        $hasser = $refl->getMethod($hasser);

        if (!$hasser->isPublic()) {
            return false;
        }

        return $hasser->getNumberOfRequiredParameters() === 0;
    }

    public function extract(object $object, string $property): mixed
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $hasser = $this
            ->hasser
            ->sprintf(Str::of($property)->camelize()->ucfirst()->toString())
            ->toString();

        /** @psalm-suppress MixedMethodCall */
        return $object->$hasser();
    }
}
