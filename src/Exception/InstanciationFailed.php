<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Exception;

final class InstanciationFailed extends \RuntimeException implements Exception
{
    public function __construct(string $class, \Throwable $e)
    {
        parent::__construct(
            \sprintf(
                'Class "%s" cannot be instanciated',
                $class,
            ),
            $e->getCode(),
            $e,
        );
    }
}
