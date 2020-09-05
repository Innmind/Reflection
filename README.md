# Reflection

[![Build Status](https://github.com/innmind/reflection/workflows/CI/badge.svg)](https://github.com/innmind/reflection/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/reflection/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/reflection)
[![Type Coverage](https://shepherd.dev/github/innmind/reflection/coverage.svg)](https://shepherd.dev/github/innmind/reflection)

Library to build objects and extract data out of them through an immutable API.

## Inject data into an object

```php
use Innmind\Reflection\ReflectionObject;

$refl = ReflectionObject::of($myObject)
    ->withProperty('foo', 'bar')
    ->withProperty('bar', 'baz');
$refl->build();
```

This simple code will inject both `foo` and `bar` into your `$myObject` following this strategy:

* look for the setter `setFoo()`
* look for a method `foo()` that has at least one argument
* use reflection to set value directly on the property

**Important**: all strategies using methods will use a camelized version of the property, ie: the property `foo_bar` will lead to `setFooBar()` and `fooBar()`.

## Build a new object

```php
use Innmind\Reflection\ReflectionClass;

class Foo
{
    private $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}

$foo = ReflectionClass::of(Foo::class)
    ->withProperty('foo', 'bar')
    ->build();
```

The `ReflectionClass` uses the `ReflectionInstanciator` to build the new instance of your class; it's replaceable by any object implementing the `Instanciator` interface and giving it as the fourth argument of `ReflectionClass`.

In case the properties you define to be injected can't be injected through the constructor, it will use internally `ReflectionObject` to do so.

## Extracting data out of an object

```php
use Innmind\Reflection\ReflectionObject;

$properties = ReflectionObject::of($myObject)->extract('foo', 'bar', 'baz');
```

Here `$properties` is a collection containing the values of `foo`, `bar` and `baz` that are set in your `$myObject`.

To do so, it uses 3 strategies:

* look for a `getFoo()`
* look for a `foo()` (without required parameters)
* look for a `isFoo()` (without required parameters)
* look for a `hasFoo()` (without required parameters)
* uses reflection to check if the property `foo` exists

**Important**: all strategies using methods will use a camelized version of the property, ie: the property `foo_bar` will lead to `getFooBar()` and `fooBar()`.
