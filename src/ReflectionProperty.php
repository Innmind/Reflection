<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\Set;

/**
 * @template T of object
 */
final class ReflectionProperty
{
    /** @var class-string<T> */
    private string $class;
    /** @var non-empty-string */
    private string $name;

    /**
     * @param class-string<T> $class
     * @param non-empty-string $name
     */
    private function __construct(string $class, string $name)
    {
        $this->class = $class;
        $this->name = $name;
    }

    /**
     * @template A
     *
     * @param class-string<A> $class
     * @param non-empty-string $name
     *
     * @return self<A>
     */
    public static function of(string $class, string $name): self
    {
        return new self($class, $name);
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function type(): ReflectionType
    {
        return ReflectionType::of((new \ReflectionProperty($this->class, $this->name))->getType());
    }

    /**
     * @return Set<ReflectionAttribute>
     */
    public function attributes(): Set
    {
        $reflection = new \ReflectionProperty($this->class, $this->name);
        /** @var Set<ReflectionAttribute> */
        $attributes = Set::of();

        foreach ($reflection->getAttributes() as $attribute) {
            $attributes = ($attributes)(ReflectionAttribute::of($attribute));
        }

        return $attributes;
    }
}
