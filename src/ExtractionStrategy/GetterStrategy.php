<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategyInterface,
    Exception\LogicException
};
use Innmind\Immutable\Str;

class GetterStrategy implements ExtractionStrategyInterface
{
    private $getter;

    public function __construct()
    {
        $this->getter = new Str('get%s');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $property): bool
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
    public function extract($object, string $property)
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
