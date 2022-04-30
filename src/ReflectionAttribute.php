<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

final class ReflectionAttribute
{
    private \ReflectionAttribute $attribute;

    private function __construct(\ReflectionAttribute $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * @internal
     */
    public static function of(\ReflectionAttribute $attribute): self
    {
        return new self($attribute);
    }

    /**
     * @return class-string
     */
    public function class(): string
    {
        /** @var class-string */
        return $this->attribute->getName();
    }

    public function instance(): object
    {
        return $this->attribute->newInstance();
    }
}
