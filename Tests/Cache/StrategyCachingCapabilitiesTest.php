<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\Cache;

use Innmind\Reflection\ExtractionStrategy\ReflectionStrategy;
use Innmind\Reflection\ReflectionObject;

/**
 * StrategyCachingCapabilitiesTest
 *
 * @author Hugues Maignol <hugues@hmlb.fr>
 */
class StrategyCachingCapabilitiesTest extends \PHPUnit_Framework_TestCase
{
    public function testCaching()
    {
        $object = new CacheTestObject('unicorn');
        $reflection = new ReflectionObject($object);

        $extractionStrategies = $reflection->getExtractionStrategies();

        //Strategy should not be already cached for CacheTestObject
        $cachedStrategies = $this->getCachedStrategies($extractionStrategies);
        foreach($cachedStrategies as $key => $strategy){
            $this->assertFalse($key == 'Innmind\Reflection\Tests\Cache\CacheTestObject::name');
        }

        $firstTime = $reflection->extract(['name']);

        //Strategy should now be cached for CacheTestObject::name
        $cachedStrategies = $this->getCachedStrategies($extractionStrategies);
        $this->assertArrayHasKey('Innmind\Reflection\Tests\Cache\CacheTestObject::name', $cachedStrategies);
        $this->assertInstanceOf(
            ReflectionStrategy::class,
            $cachedStrategies['Innmind\Reflection\Tests\Cache\CacheTestObject::name']
        );

        $secondTime = $reflection->extract(['name']);

        //Strategy should still be cached for CacheTestObject::name and returned strategies should be equal.
        $cachedStrategies = $this->getCachedStrategies($extractionStrategies);
        $this->assertArrayHasKey('Innmind\Reflection\Tests\Cache\CacheTestObject::name', $cachedStrategies);
        $this->assertInstanceOf(
            ReflectionStrategy::class,
            $cachedStrategies['Innmind\Reflection\Tests\Cache\CacheTestObject::name']
        );
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
