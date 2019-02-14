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
    /**
    * @psalm-return Generator<int, array{0:string, 1:class-string<Exception>, 2:string, 3:array<int, scalar>, 4:int, 5:class-string<Throwable>|null, 6:string, 7:int}, mixed, void>
    */
    public function DataProviderInvalidArgumentExceptionBad() : Generator
    {
        yield from array_map(
            /**
            * @psalm-param array{0:string, 1:class-string<InvalidArgumentException>, 2:string, 3:array<int, scalar>, 4:int, 5:class-string<Throwable>|null, 6:string, 7:int} $args
            *
            * @psalm-return array{0:string, 1:class-string<Exception>, 2:string, 3:array<int, scalar>, 4:int, 5:class-string<Throwable>|null, 6:string, 7:int}
            */
            function (array $args) : array {
                $args[self::ARG_SECOND] = Exception::class;

                return $args;
            },
            $this->DataProviderInvalidArgumentException()
        );
    }

    public function test_Paranoid_DataProviderInvalidArgumentExceptionBad()
    {
        $good = $this->DataProviderInvalidArgumentException();

        foreach ($this->DataProviderInvalidArgumentExceptionBad() as $i => $args) {
            static::assertInternalType('array', $args);
            static::assertNotSame($args[self::ARG_SECOND], $good[$i][self::ARG_SECOND]);
        }
    }

    /**
    * @psalm-param class-string<InvalidArgumentException> $type
    * @psalm-param class-string<Throwable>|null $previousType
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
    * @psalm-param class-string<InvalidArgumentException> $type
    * @psalm-param class-string<Throwable>|null $previousType
    * @param array<int, scalar> $args
    *
    * @dataProvider DataProviderInvalidArgumentExceptionBad
    *
    * @depends test_Paranoid_DataProviderInvalidArgumentExceptionBad
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
