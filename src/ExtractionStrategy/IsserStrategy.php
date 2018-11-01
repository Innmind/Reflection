<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy,
    Exception\LogicException,
};
use Innmind\Immutable\Str;

final class IsserStrategy implements ExtractionStrategy
{
    private $isser;

    public function __construct()
    {
        $this->isser = new Str('is%s');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property): bool
    {
        $refl = new \ReflectionObject($object);
        $isser = (string) $this->isser->sprintf(
            (string) (new Str($property))->camelize()
        );

        if (!$refl->hasMethod($isser)) {
            return false;
        }

        $isser = $refl->getMethod($isser);

        if (!$isser->isPublic()) {
            return false;
        }

        return $isser->getNumberOfRequiredParameters() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(object $object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $isser = (string) $this->isser->sprintf(
            (string) (new Str($property))->camelize()
        );

        return $object->$isser();
    }
}
