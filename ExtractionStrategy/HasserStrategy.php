<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\Exception\LogicException;
use Innmind\Immutable\StringPrimitive;

class HasserStrategy implements ExtractionStrategyInterface
{
    private $hasser;

    public function __construct()
    {
        $this->hasser = new StringPrimitive('has%s');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $property): bool
    {
        $refl = new \ReflectionObject($object);
        $hasser = (string) $this->hasser->sprintf(
            (string) (new StringPrimitive($property))->camelize()
        );

        return $refl->hasMethod($hasser) &&
            $refl->getMethod($hasser)->getNumberOfRequiredParameters() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $hasser = (string) $this->hasser->sprintf(
            (string) (new StringPrimitive($property))->camelize()
        );

        return $object->$hasser();
    }
}
