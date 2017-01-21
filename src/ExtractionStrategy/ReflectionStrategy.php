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
        try {
            $this->property($object, $property);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function extract($object, string $property)
    {
        if (!$this->supports($object, $property)) {
            throw new LogicException;
        }

        $refl = $this->property($object, $property);

        if (!$refl->isPublic()) {
            $refl->setAccessible(true);
        }

        $value = $refl->getValue($object);

        if (!$refl->isPublic()) {
            $refl->setAccessible(false);
        }

        return $value;
    }

    private function property($object, string $property): \ReflectionProperty
    {
        try {
            return $this->objectProperty($object, $property);
        } catch (\Exception $e) {
            return $this->classProperty(get_class($object), $property);
        }
    }

    private function objectProperty($object, string $property): \ReflectionProperty
    {
        $refl = new \ReflectionObject($object);

        if ($refl->hasProperty($property)) {
            return $refl->getProperty($property);
        }

        throw new \Exception;
    }

    private function classProperty(string $class, string $property): \ReflectionProperty
    {
        $refl = new \ReflectionClass($class);

        if ($refl->hasProperty($property)) {
            return $refl->getProperty($property);
        }

        if ($refl->getParentClass()) {
            return $this->property($refl->getParentClass()->getName(), $property);
        }

        throw new \Exception;
    }
}
