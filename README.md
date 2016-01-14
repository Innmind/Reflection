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
