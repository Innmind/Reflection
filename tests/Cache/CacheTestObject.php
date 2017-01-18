<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\Cache;

/**
 * CacheTestObject
 *
 * @author Hugues Maignol <hugues@hmlb.fr>
 */
class CacheTestObject
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

}
