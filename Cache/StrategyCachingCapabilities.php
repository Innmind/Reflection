<?php

namespace Innmind\Reflection\Cache;

/**
 * Strategy caching based on object class and key of method/property.
 *
 * @author Hugues Maignol <hugues@hmlb.frr>
 */
trait StrategyCachingCapabilities
{
    private static $strategyCache = [];

    /**
     * The cached strategy for the given class and property.
     *
     * @param string $class
     * @param string $key
     *
     * @return mixed|null
     */
    private function getCachedStrategy(string $class, string $key)
    {
        return self::$strategyCache[$this->getCacheKey($class, $key)] ?? null;
    }

    /**
     * @param string $class
     * @param string $key
     * @param mixed  $strategy
     *
     * @return self
     */
    private function setCachedStrategy(string $class, string $key, $strategy): self
    {
        self::$strategyCache[$class.'::'.$key] = $strategy;

        return $this;
    }

    /**
     * Computes cache key for class and property.
     *
     * @param string $class
     * @param string $key
     *
     * @return string
     */
    private function getCacheKey(string $class, string $key): string
    {
        return $class.'::'.$key;
    }
}
