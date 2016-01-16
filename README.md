# Reflection

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
