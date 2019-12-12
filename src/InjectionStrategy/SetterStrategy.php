<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategy,
    Exception\LogicException,
};
use Innmind\Immutable\Str;

final class SetterStrategy implements InjectionStrategy
{
    private $setter;

    public function __construct()
    {
        $this->setter = Str::of('set%s');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property, $value): bool
    {
        $refl = new \ReflectionObject($object);
        $setter = $this
            ->setter
            ->sprintf(Str::of($property)->camelize()->ucfirst()->toString())
            ->toString();

        if (!$refl->hasMethod($setter)) {
            return false;
        }

        return $refl->getMethod($setter)->isPublic();
    }

    /**
     * {@inheritdoc}
     */
    public function inject(object $object, string $property, $value): object
    {
        if (!$this->supports($object, $property, $value)) {
            throw new LogicException;
        }

        $setter = $this
            ->setter
            ->sprintf(Str::of($property)->camelize()->ucfirst()->toString())
            ->toString();
        $object->$setter($value);

        return $object;
    }
}
