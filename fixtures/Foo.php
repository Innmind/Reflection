<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Reflection;

class Foo
{
    protected $someProperty = 42;

    public function someProperty(int $newValue = null)
    {
        if (is_int($newValue)) {
            $this->someProperty = $newValue;

            return $this;
        }

        return $this->someProperty;
    }

    public function getSomeProperty()
    {
        return $this->someProperty;
    }

    public function hasSomeProperty()
    {
        return true;
    }

    public function isSomeProperty()
    {
        return false;
    }

    public function setSomeProperty($value)
    {
        $this->someProperty = $value;

        return $this;
    }
}
