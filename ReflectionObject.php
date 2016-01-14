<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\InvalidArgumentException;
use Innmind\Reflection\Exception\LogicException;
use Innmind\Immutable\Collection;
use Innmind\Immutable\CollectionInterface;
use Innmind\Immutable\StringPrimitive;

class ReflectionObject
{
    private $object;
    private $properties;
    private $setter;

    public function __construct(
        $object,
        CollectionInterface $properties = null
    ) {
        if (!is_object($object)) {
            throw new InvalidArgumentException;
        }

        $this->object = $object;

        if ($properties === null) {
            $properties = new Collection([]);
        }

        $this->properties = $properties;
        $this->setter = new StringPrimitive('set%s');
    }

    /**
     * Add a property that will be injected
     *
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    public function withProperty(string $name, $value)
    {
        return new self(
            $this->object,
            $this->properties->set($name, $value)
        );
    }

    /**
     * Return the collection of properties that will be injected in the object
     *
     * @return CollectionInterface
     */
    public function getProperties() : CollectionInterface
    {
        return $this->properties;
    }

    /**
     * Return the object with the list of properties set on it
     *
     * @return object
     */
    public function buildObject()
    {
        foreach ($this->properties as $key => $value) {
            $this->inject($key, $value);
        }

        return $this->object;
    }

    /**
     * Inject the given key/value pair into the object
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    private function inject(string $key, $value)
    {
        $refl = new \ReflectionObject($this->object);

        $setter = $this->setter->sprintf(ucfirst($key));

        if ($refl->hasMethod((string) $setter)) {
            $this->injectBySetter((string) $setter, $value);
        } else if (
            $refl->hasMethod($key) &&
            $refl->getMethod($key)->getNumberOfParameters() > 0
        ) {
            $this->injectBySetter($key, $value);
        } else if ($refl->hasProperty($key)) {
            $this->injectByReflection(
                $refl->getProperty($key),
                $value
            );
        } else {
            throw new LogicException(sprintf(
                'Property "%s" not found',
                $key
            ));
        }
    }

    /**
     * Inject the value through the given setter
     *
     * @param string $setter
     * @param mixed $value
     *
     * @return void
     */
    private function injectBySetter(string $setter, $value)
    {
        $this->object->$setter($value);
    }

    /**
     * Inject the given value through reflection
     *
     * @param ReflectionProperty $refl
     * @param mixed $value
     *
     * @return void
     */
    public function injectByReflection(\ReflectionProperty $refl, $value)
    {
        if (!$refl->isPublic()) {
            $refl->setAccessible(true);
        }

        $refl->setValue($this->object, $value);

        if (!$refl->isPublic()) {
            $refl->setAccessible(false);
        }
    }
}
