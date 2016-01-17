<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategyInterface;
use Innmind\Reflection\Exception\LogicException;

class ReflectionStrategy implements InjectionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object, string $property, $value): bool
    {
        $refl = new \ReflectionObject($object);

        return $refl->hasProperty($property);
    }

    /**
     * {@inheritdoc}
     */
    public function inject($object, string $property, $value)
    {
        if (!$this->supports($object, $property, $value)) {
            throw new LogicException;
        }

        $refl = new \ReflectionObject($object);
        $refl = $refl->getProperty($property);

        if (!$refl->isPublic()) {
            $refl->setAccessible(true);
        }

        $refl->setValue($object, $value);

        if (!$refl->isPublic()) {
            $refl->setAccessible(false);
        }
    }
}
