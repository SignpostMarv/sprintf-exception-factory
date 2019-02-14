<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\SprintfExceptionFactory;

use Exception;
use InvalidArgumentException;
use Throwable;

abstract class SprintfExceptionFactory
{
    const DEFAULT_INT_CODE = 0;

    const IS_A_STRINGS = true;

    /**
    * @template T as Exception
    *
    * @psalm-param T::class|null $type
    * @psalm-param T::class $expected
    *
    * @param scalar ...$args
    *
    * @throws InvalidArgumentException if $type is not an implementation of $expected
    *
    * @return T
    */
    public static function Exception(
        string $type = null,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null,
        string $expected = Exception::class,
        string $sprintf = '%s',
        ...$args
    ) : Exception {
        $type = $type ?? Exception::class;

        if ($type !== $expected && ! is_a($type, $expected, self::IS_A_STRINGS)) {
            throw static::ExpectArgumentIsException(
                $type,
                $expected,
                1,
                __METHOD__,
                $code,
                $previous
            );
        }

        return new $type(sprintf($sprintf, ...$args), $code, $previous);
    }

    /**
    * @template T as InvalidArgumentException
    *
    * @psalm-param T::class|null $type
    *
    * @param scalar ...$args
    *
    * @throws InvalidArgumentException if $type is not an InvalidArgumentException implementation
    *
    * @return T
    */
    public static function InvalidArgumentException(
        string $type = null,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null,
        string $sprintf = '%s',
        ...$args
    ) : InvalidArgumentException {
        return static::Exception(
            $type ?? InvalidArgumentException::class,
            $code,
            $previous,
            InvalidArgumentException::class,
            $sprintf,
            ...$args
        );
    }

    /**
    * Since we pass null to static::InvalidArgumentException($type) then
    *  static::Exception() receives class-string<InvalidArgumentException> by default,
    *  so @throws is only a technicality.
    *
    * @throws InvalidArgumentException but not really
    */
    public static function ExpectArgumentIsException(
        string $type,
        string $expected,
        int $argument,
        string $method,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : InvalidArgumentException {
        return static::InvalidArgumentException(
            InvalidArgumentException::class,
            $code,
            $previous,
            'Argument %u passed to %s() must be an implementation of %s, %s given!',
            $argument,
            $method,
            $expected,
            $type
        );
    }
}
