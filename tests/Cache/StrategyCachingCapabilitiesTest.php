<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\Cache;

use Innmind\Reflection\ExtractionStrategy\ReflectionStrategy;
use Innmind\Reflection\ReflectionObject;
use PHPUnit\Framework\TestCase;

/**
 * StrategyCachingCapabilitiesTest
 *
 * @author Hugues Maignol <hugues@hmlb.fr>
 */
class StrategyCachingCapabilitiesTest extends TestCase
{
    public function testCaching()
    {
        $object = new CacheTestObject('unicorn');
        $reflection = new ReflectionObject($object);

        $extractionStrategies = $reflection->extractionStrategies();

        //Strategy should not be already cached for CacheTestObject
        $cachedStrategies = $this->getCachedStrategies($extractionStrategies);
        foreach ($cachedStrategies as $key => $strategy) {
            $this->assertFalse($key == 'Tests\Innmind\Reflection\Cache\CacheTestObject::name');
        }

        $firstTime = $reflection->extract(['name'])['name'];
        $this->assertEquals('unicorn', $firstTime);

        //Strategy should now be cached for CacheTestObject::name
        $cachedStrategies = $this->getCachedStrategies($extractionStrategies);
        $this->assertArrayHasKey('Tests\Innmind\Reflection\Cache\CacheTestObject::name', $cachedStrategies);
        $this->assertInstanceOf(
            ReflectionStrategy::class,
            $cachedStrategies['Tests\Innmind\Reflection\Cache\CacheTestObject::name']
        );

        $secondReflection = new ReflectionObject($object);
        $secondExtractionStrategies = $secondReflection->extractionStrategies();

        //Strategy should still be cached for CacheTestObject::name and returned strategies should be equal.
        $secondCachedStrategies = $this->getCachedStrategies($secondExtractionStrategies);
        $this->assertArrayHasKey('Tests\Innmind\Reflection\Cache\CacheTestObject::name', $secondCachedStrategies);
        $this->assertInstanceOf(
            ReflectionStrategy::class,
            $secondCachedStrategies['Tests\Innmind\Reflection\Cache\CacheTestObject::name']
        );
        $this->assertEquals($cachedStrategies, $secondCachedStrategies);

        $secondTime = $secondReflection->extract(['name'])['name'];

        $this->assertEquals($firstTime, $secondTime);
    }

    private function getCachedStrategies($strategyCache)
    {
        $reflection = new \ReflectionClass($strategyCache);
        $property = $reflection->getProperty('strategyCache');
        $property->setAccessible(true);
        $cache = $property->getValue($strategyCache);
        $property->setAccessible(false);

        return $cache;
    }
}
