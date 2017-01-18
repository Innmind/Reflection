<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\Exception\LogicException;
use Innmind\Immutable\StringPrimitive;

class IsserStrategy implements ExtractionStrategyInterface
{
    private $isser;

    public function __construct()
    {
        $this->isser = new StringPrimitive('is%s');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $property): bool
    {
        $refl = new \ReflectionObject($object);
        $isser = (string) $this->isser->sprintf(
            (string) (new StringPrimitive($property))->camelize()
        );

        return $refl->hasMethod($isser) &&
            $refl->getMethod($isser)->getNumberOfRequiredParameters() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $isser = (string) $this->isser->sprintf(
            (string) (new StringPrimitive($property))->camelize()
        );

        return $object->$isser();
    }
}
