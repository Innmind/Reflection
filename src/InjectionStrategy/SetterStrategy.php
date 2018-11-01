<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategy,
    Exception\LogicException
};
use Innmind\Immutable\Str;

class SetterStrategy implements InjectionStrategy
{
    private $setter;

    public function __construct()
    {
        $this->setter = new Str('set%s');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property, $value): bool
    {
        $refl = new \ReflectionObject($object);
        $setter = (string) $this->setter->sprintf(
            (string) (new Str($property))->camelize()
        );

        if (!$refl->hasMethod($setter)) {
            return false;
        }

        return $refl->getMethod($setter)->isPublic();
    }

    /**
     * {@inheritdoc}
     */
    public function inject(object $object, string $property, $value): void
    {
        if (!$this->supports($object, $property, $value)) {
            throw new LogicException;
        }

        $setter = (string) $this->setter->sprintf(
            (string) (new Str($property))->camelize()
        );
        $object->$setter($value);
    }
}
