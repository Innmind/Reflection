<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\Set;

final class ReflectionType
{
    private ?\ReflectionType $type;

    private function __construct(?\ReflectionType $type)
    {
        $this->type = $type;
    }

    /**
     * @internal
     */
    public static function of(?\ReflectionType $type): self
    {
        return new self($type);
    }

    public function allows(mixed $value): bool
    {
        if (\is_null($this->type)) {
            return true;
        }

        return $this->allowsType($this->type, $value);
    }

    public function toString(): string
    {
        if (\is_null($this->type)) {
            return 'mixed';
        }

        return (string) $this->type;
    }

    private function allowsType(\ReflectionType $type, mixed $value): bool
    {
        return match ($type::class) {
            \ReflectionNamedType::class => $this->allowsNamed($type, $value),
            \ReflectionUnionType::class => Set::of(...$type->getTypes())->any(fn($type) => $this->allowsType($type, $value)),
            \ReflectionIntersectionType::class => Set::of(...$type->getTypes())->matches(fn($type) => $this->allowsType($type, $value)),
        };
    }

    private function allowsNamed(\ReflectionNamedType $type, mixed $value): bool
    {
        if ($type->allowsNull() && \is_null($value)) {
            return true;
        }

        if ($type->getName() === 'mixed') {
            return true;
        }

        if ($type->isBuiltin()) {
            return $type->getName() === $this->typeOf($value);
        }

        $class = $type->getName();

        return $value instanceof $class;
    }

    private function typeOf(mixed $value): string
    {
        $type = \gettype($value);

        return match ($type) {
            'integer' => 'int',
            'boolean' => 'bool',
            'double' => 'float',
            default => $type,
        };
    }
}
