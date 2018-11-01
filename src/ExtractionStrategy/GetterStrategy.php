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
    private $getter;

    public function __construct()
    {
        $this->getter = new Str('get%s');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property): bool
    {
        $refl = new \ReflectionObject($object);
        $getter = (string) $this->getter->sprintf(
            (string) (new Str($property))->camelize()
        );

        if (!$refl->hasMethod($getter)) {
            return false;
        }

        $getter = $refl->getMethod($getter);

        if (!$getter->isPublic()) {
            return false;
        }

        return $getter->getNumberOfRequiredParameters() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(object $object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $getter = (string) $this->getter->sprintf(
            (string) (new Str($property))->camelize()
        );

        return $object->$getter();
    }
}
