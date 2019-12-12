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
    private $hasser;

    public function __construct()
    {
        $this->hasser = Str::of('has%s');
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function extract(object $object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $hasser = $this
            ->hasser
            ->sprintf(Str::of($property)->camelize()->ucfirst()->toString())
            ->toString();

        return $object->$hasser();
    }
}
