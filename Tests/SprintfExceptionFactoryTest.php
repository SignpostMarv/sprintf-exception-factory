<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\SprintfExceptionFactory\Tests;

use Exception;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SignpostMarv\SprintfExceptionFactory\SprintfExceptionFactory;
use Throwable;

class SprintfExceptionFactoryTest extends TestCase
{
    /**
    * @return array<int, array{0:string, 1:class-string<InvalidArgumentException>, 2:string, 3:array<int, scalar>, 4:int, 5:class-string<Throwable>|null, 6:string, 7:int}>
    */
    public function DataProviderInvalidArgumentException() : array
    {
        return [
            [
                'foo bar',
                InvalidArgumentException::class,
                'foo %s',
                [
                    'bar',
                ],
                0,
                null,
                '',
                0,
            ],
        ];
    }

    public function DataProviderInvalidArgumentExceptionBad() : Generator
    {
        foreach ($this->DataProviderInvalidArgumentException() as $args) {
            $args[1] = Exception::class;

            yield $args;
        }
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
        int $code = 0,
        string $previousType = null,
        string $previousMessage = '',
        int $previousCode = 0
    ) {
        $previous = static::MaybeObtainThrowable($previousType, $previousMessage, $previousCode);

        $result = SprintfExceptionFactory::InvalidArgumentException(
            $type,
            $code,
            $previous,
            $sprintf,
            ...$args
        );

        if (InvalidArgumentException::class !== $type) {
            static::assertInstanceOf($type, $result);
        }

        static::PerformAssertions(
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

        $result = SprintfExceptionFactory::Exception(
            $type,
            $code,
            $previous,
            InvalidArgumentException::class,
            $sprintf,
            ...$args
        );

        if (InvalidArgumentException::class !== $type) {
            static::assertInstanceOf($type, $result);
        }

        static::PerformAssertions(
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
        int $code = 0,
        string $previousType = null,
        string $previousMessage = '',
        int $previousCode = 0
    ) {
        $previous = static::MaybeObtainThrowable($previousType, $previousMessage, $previousCode);

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

        $result = SprintfExceptionFactory::InvalidArgumentException(
            $type,
            $code,
            $previous,
            $sprintf,
            ...$args
        );
    }

    /**
    * @param class-string<Throwable> $type
    * @param class-string<Throwable>|null $previousType
    * @param array<int, scalar> $args
    */
    protected function PerformAssertions(
        Throwable $result,
        Throwable $expectedPrevious = null,
        string $expectedMessage = '',
        string $type = Throwable::class,
        string $sprintf = '%s',
        array $args = [''],
        int $code = 0,
        string $previousType = null,
        string $previousMessage = '',
        int $previousCode = 0
    ) {
        static::assertSame($expectedMessage, $result->getMessage());
        static::assertSame($code, $result->getCode());

        $resultPrevious = $result->getPrevious();

        static::assertSame($expectedPrevious, $resultPrevious);

        if ( ! is_null($previousType)) {
            static::assertInstanceOf($previousType, $resultPrevious);
        } else {
            static::assertNull($resultPrevious);
        }

        if ( ! is_null($expectedPrevious)) {
            static::assertInstanceOf(Throwable::class, $resultPrevious);
            static::assertSame($previousMessage, $resultPrevious->getMessage());
            static::assertSame($previousCode, $resultPrevious->getCode());
        }
    }

    /**
    * @param class-string<Throwable>|null $previousType
    *
    * @return Throwable|null
    */
    protected static function MaybeObtainThrowable(
        string $previousType = null,
        string $previousMessage = '',
        int $previousCode = 0
    ) {
        $previous = null;

        if ( ! is_null($previousType)) {
            $previous = new $previousType($previousMessage, $previousCode);
        }

        return $previous;
    }
}
