<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy,
    Exception\LogicException,
    Visitor\AccessProperty,
};

final class ReflectionStrategy implements ExtractionStrategy
{
    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property): bool
    {
        try {
            (new AccessProperty)($object, $property);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function extract(object $object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $refl = (new AccessProperty)($object, $property);

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
