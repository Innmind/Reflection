<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategy,
    Exception\LogicException,
    Visitor\AccessProperty
};

class ReflectionStrategy implements InjectionStrategy
{
    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property, $value): bool
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
    public function inject(object $object, string $property, $value): void
    {
        if (!$this->supports($object, $property, $value)) {
            throw new LogicException;
        }

        $refl = (new AccessProperty)($object, $property);

        if (!$refl->isPublic()) {
            $refl->setAccessible(true);
        }

        $refl->setValue($object, $value);

        if (!$refl->isPublic()) {
            $refl->setAccessible(false);
        }
    }
}
