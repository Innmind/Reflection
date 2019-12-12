<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Exception;

final class PropertyCannotBeExtracted extends LogicException
{
    public function __construct(string $property)
    {
        parent::__construct(
            \sprintf(
                'Property "%s" cannot be extracted',
                $property,
            ),
        );
    }
}
