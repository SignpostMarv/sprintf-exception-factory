<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\SprintfExceptionFactory;

use BadMethodCallException;
use Exception;
use InvalidArgumentException;
use RuntimeException;
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
    * @param string|int|float ...$args
    *
    * @throws InvalidArgumentException if $type is not an implementation of $expected
    *
    * @return Exception
    *
    * @psalm-return T
    */
    public static function Exception(
        ? string $type,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null,
        string $expected = Exception::class,
        string $sprintf = '%s',
        ...$args
    ) : Exception {
        /**
        * @psalm-var class-string<Exception>
        */
        $type = $type ?? Exception::class;

        if ( ! is_a($type, $expected, self::IS_A_STRINGS)) {
            throw static::ExpectArgumentIsException($type, $expected, 1, __METHOD__, $code, $previous);
        }

        /** @var T */
        return new $type(sprintf($sprintf, ...$args), $code, $previous);
    }

    /**
    * @template T as BadMethodCallException
    *
    * @psalm-param T::class|null $type
    *
    * @param string|int|float ...$args
    *
    * @throws BadMethodCallException if $type is not an BadMethodCallException implementation
    *
    * @return BadMethodCallException
    *
    * @psalm-return T
    */
    public static function BadMethodCallException(
        ? string $type,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null,
        string $sprintf = '%s',
        ...$args
    ) : BadMethodCallException {
        /**
        * @psalm-var class-string<BadMethodCallException>
        */
        $new_type = $type ?? BadMethodCallException::class;

        /**
        * @var BadMethodCallException
        *
        * @psalm-var T
        */
        $out = static::Exception(
            $new_type,
            $code,
            $previous,
            BadMethodCallException::class,
            $sprintf,
            ...$args
        );

        return $out;
    }

    /**
    * @template T as InvalidArgumentException
    *
    * @psalm-param T::class|null $type
    *
    * @param string|int|float ...$args
    *
    * @throws InvalidArgumentException if $type is not an InvalidArgumentException implementation
    *
    * @return InvalidArgumentException
    *
    * @psalm-return T
    */
    public static function InvalidArgumentException(
        ? string $type,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null,
        string $sprintf = '%s',
        ...$args
    ) : InvalidArgumentException {
        /**
        * @psalm-var class-string<InvalidArgumentException>
        */
        $new_type = $type ?? InvalidArgumentException::class;

        /**
        * @var InvalidArgumentException
        *
        * @psalm-var T
        */
        $out = static::Exception(
            $new_type,
            $code,
            $previous,
            InvalidArgumentException::class,
            $sprintf,
            ...$args
        );

        return $out;
    }

    /**
    * @template T as RuntimeException
    *
    * @psalm-param T::class|null $type
    *
    * @param string|int|float ...$args
    *
    * @throws RuntimeException if $type is not an RuntimeException implementation
    *
    * @return RuntimeException
    *
    * @psalm-return T
    */
    public static function RuntimeException(
        ? string $type,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null,
        string $sprintf = '%s',
        ...$args
    ) : RuntimeException {
        /**
        * @psalm-var class-string<RuntimeException>
        */
        $new_type = $type ?? RuntimeException::class;

        /**
        * @var RuntimeException
        *
        * @psalm-var T
        */
        $out = static::Exception(
            $new_type,
            $code,
            $previous,
            RuntimeException::class,
            $sprintf,
            ...$args
        );

        return $out;
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
