<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Reflection;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Attr
{
    public function __construct(
        public string $value,
    ) {
    }
}
