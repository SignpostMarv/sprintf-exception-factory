<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\SprintfExceptionFactory\Tests;

use Exception;
use Generator;
use InvalidArgumentException;
use SignpostMarv\SprintfExceptionFactory\SprintfExceptionFactory;
use Throwable;

class InvalidArgumentExceptionTest extends SprintfExceptionFactoryTest
{
    public function DataProviderInvalidArgumentExceptionBad() : Generator
    {
        yield from array_map(
            function (array $args) : array {
                $args[self::ARG_SECOND] = Exception::class;

                return $args;
            },
            $this->DataProviderInvalidArgumentException()
        );
    }

    /**
    * @param class-string<InvalidArgumentException> $type
    * @param class-string<Throwable>|null $previousType
    * @param array<int, scalar> $args
    *
    * @dataProvider DataProviderInvalidArgumentException
    */
    public function testInvalidArgumentException(
        string $expectedMessage,
        string $type,
        string $sprintf,
        array $args,
        int $code = SprintfExceptionFactory::DEFAULT_INT_CODE,
        string $previousType = null,
        string $previousMessage = '',
        int $previousCode = SprintfExceptionFactory::DEFAULT_INT_CODE
    ) {
        $previous = static::MaybeObtainThrowable($previousType, $previousMessage, $previousCode);

        $result = SprintfExceptionFactory::InvalidArgumentException(
            $type,
            $code,
            $previous,
            $sprintf,
            ...$args
        );

        $this->PerformAssertions(
            $result,
            $previous,
            $expectedMessage,
            $type,
            $sprintf,
            $args,
            $code,
            $previousType,
            $previousMessage,
            $previousCode
        );

        $this->testException(
            $expectedMessage,
            $type,
            $sprintf,
            $args,
            $code,
            $previousType,
            $previousMessage,
            $previousCode
        );

        $this->testInvalidArgumentExceptionFails(
            $expectedMessage,
            Exception::class,
            $sprintf,
            $args,
            $code,
            $previousType,
            $previousMessage,
            $previousCode
        );
    }

    /**
    * @param class-string<InvalidArgumentException> $type
    * @param class-string<Throwable>|null $previousType
    * @param array<int, scalar> $args
    *
    * @dataProvider DataProviderInvalidArgumentExceptionBad
    */
    public function testInvalidArgumentExceptionFails(
        string $expectedMessage,
        string $type,
        string $sprintf,
        array $args,
        int $code = SprintfExceptionFactory::DEFAULT_INT_CODE,
        string $previousType = null,
        string $previousMessage = '',
        int $previousCode = SprintfExceptionFactory::DEFAULT_INT_CODE
    ) {
        $previous = static::MaybeObtainThrowable($previousType, $previousMessage, $previousCode);

        static::assertSame($expectedMessage, sprintf($sprintf, ...$args));

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            SprintfExceptionFactory::class .
            '::Exception() must be an implementation of ' .
            InvalidArgumentException::class .
            ', ' .
            $type .
            ' given!'
        );

        SprintfExceptionFactory::InvalidArgumentException(
            $type,
            $code,
            $previous,
            $sprintf,
            ...$args
        );
    }
}
