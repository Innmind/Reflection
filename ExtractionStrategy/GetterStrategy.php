<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategyInterface;
use Innmind\Reflection\Exception\LogicException;
use Innmind\Immutable\StringPrimitive;

class GetterStrategy implements ExtractionStrategyInterface
{
    private $getter;

    public function __construct()
    {
        $this->getter = new StringPrimitive('get%s');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $property): bool
    {
        $refl = new \ReflectionObject($object);
        $getter = (string) $this->getter->sprintf(ucfirst($property));

        return $refl->hasMethod($getter) &&
            $refl->getMethod($getter)->getNumberOfRequiredParameters() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $getter = (string) $this->getter->sprintf(ucfirst($property));

        return $object->$getter();
    }
}
