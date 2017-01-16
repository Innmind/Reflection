<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\Exception\LogicException;

class ReflectionStrategy implements ExtractionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object, string $property): bool
    {
        $refl = new \ReflectionObject($object);

        return $refl->hasProperty($property);
    }

    /**
     * {@inheritdoc}
     */
    public function extract($object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $refl = new \ReflectionObject($object);
        $refl = $refl->getProperty($property);

        if (!$refl->isPublic()) {
            $refl->setAccessible(true);
        }

        $value = $refl->getValue($object);

        if (!$refl->isPublic()) {
            $refl->setAccessible(false);
        }

        return $value;
    }
}
