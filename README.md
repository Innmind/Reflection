# Reflection

[![Build Status](https://github.com/innmind/reflection/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/reflection/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/reflection/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/reflection)
[![Type Coverage](https://shepherd.dev/github/innmind/reflection/coverage.svg)](https://shepherd.dev/github/innmind/reflection)

Library to build objects and extract data out of them.

## Build and inject data into an object

```php
use Innmind\Reflection\Instanciate;
use Innmind\Immutable\{
    Map,
    Maybe,
};

final class Foo
{
    private int $foo;
    private mixed $bar;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}

$object = (new Instanciate)(Foo::class, Map::of(
    ['foo', 42],
    ['bar', 'baz'],
)); // Maybe<Foo>
```

This code will create a new `Foo` object and assign the property `foo` to `42` and `bar` to `'baz'`.

## Extracting data out of an object

```php
use Innmind\Reflection\Extract;
use Innmind\Immutable\{
    Set,
    Maybe,
};

$properties = (new Extract)($myObject, Set::of('foo', 'bar', 'baz')); // Maybe<Map<non-empty-string, mixed>>
```
