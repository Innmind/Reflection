<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Type\{
    Type,
    Build,
};

final class ReflectionType
{
    private Type $type;

    private function __construct(Type $type)
    {
        $this->type = $type;
    }

    /**
     * @internal
     */
    public static function of(?\ReflectionType $type): self
    {
        return new self(Build::fromReflection($type));
    }

    public function allows(mixed $value): bool
    {
        return $this->type->allows($value);
    }

    public function toString(): string
    {
        return $this->type->toString();
    }
}
