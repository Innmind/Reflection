# Reflection

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Reflection/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Reflection/?branch=develop)
[![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Reflection/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Reflection/?branch=develop)
[![Build Status](https://scrutinizer-ci.com/g/Innmind/Reflection/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Reflection/build-status/develop)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/08bda127-8c81-4f20-a5e2-c2ac37abff71/big.png)](https://insight.sensiolabs.com/projects/08bda127-8c81-4f20-a5e2-c2ac37abff71)

Library to build objects and extract data out of them through an immutable API.

## Inject data into an object

```php
use Innmind\Reflection\ReflectionObject;

$refl = (new ReflectionObject($myObject))
    ->withProperty('foo', 'bar')
    ->withProperty('bar', 'baz');
$refl->buildObject();
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

$foo = (new ReflectionClass(Foo:class))
    ->withProperty('foo', 'bar')
    ->buildObject();
```

The `ReflectionClass` uses the `ReflectionInstanciator` to build the new instance of your class; it's replaceable by any object implementing the `InstanciatorInterface` and giving it as the fourth argument of `ReflectionClass`.

In case the properties you define to be injected can't be injected through the constructor, it will use internally `ReflectionObject` to do so.

## Extracting data out of an object

```php
use Innmind\Reflection\ReflectionObject;

$properties = (new ReflectionObject($myObject))->extract(['foo', 'bar', 'baz']);
```

Here `$properties` is a collection containing the values of `foo`, `bar` and `baz` that are set in your `$myObject`.

To do so, it uses 3 strategies:

* look for a `getFoo()`
* look for a `foo()` (without required parameters)
* look for a `isFoo()` (without required parameters)
* look for a `hasFoo()` (without required parameters)
* uses reflection to check if the property `foo` exists

**Important**: all strategies using methods will use a camelized version of the property, ie: the property `foo_bar` will lead to `getFooBar()` and `fooBar()`.
