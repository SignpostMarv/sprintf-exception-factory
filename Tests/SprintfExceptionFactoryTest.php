<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\SprintfExceptionFactory\Tests;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SignpostMarv\SprintfExceptionFactory\SprintfExceptionFactory;
use Throwable;

class SprintfExceptionFactoryTest extends TestCase
{
    const ARG_SECOND = 1;

    /**
    * @psalm-return array<int, array{0:string, 1:class-string<InvalidArgumentException>, 2:string, 3:array<int, scalar>, 4:int, 5:class-string<Throwable>|null, 6:string, 7:int}>
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
                SprintfExceptionFactory::DEFAULT_INT_CODE,
                Exception::class,
                'baz',
                SprintfExceptionFactory::DEFAULT_INT_CODE,
            ],
            [
                'foo bar',
                InvalidArgumentException::class,
                'foo %s',
                [
                    'bar',
                ],
                SprintfExceptionFactory::DEFAULT_INT_CODE,
                null,
                '',
                SprintfExceptionFactory::DEFAULT_INT_CODE,
            ],
        ];
    }

    public function test_Paranoid_DataProviderInvalidArgumentException()
    {
        foreach ($this->DataProviderInvalidArgumentException() as $args) {
            static::assertIsArray($args);
        }
    }

    /**
    * @psalm-param class-string<Exception> $type
    * @psalm-param class-string<Throwable>|null $previousType
    *
    * @param array<int, scalar> $args
    *
    * @dataProvider DataProviderInvalidArgumentException
    */
    public function testException(
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

        if (is_null($previousType)) {
            static::assertNull($previous);
        }

        $result = SprintfExceptionFactory::Exception(
            $type,
            $code,
            $previous,
            InvalidArgumentException::class,
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
    }

    /**
    * @psalm-param class-string<Throwable> $type
    * @psalm-param class-string<Throwable>|null $previousType
    *
    * @param array<int, scalar> $args
    */
    protected function PerformAssertions(
        Throwable $result,
        Throwable $expectedPrevious = null,
        string $expectedMessage = '',
        string $type = Throwable::class,
        string $sprintf = '%s',
        array $args = [''],
        int $code = SprintfExceptionFactory::DEFAULT_INT_CODE,
        string $previousType = null,
        string $previousMessage = '',
        int $previousCode = SprintfExceptionFactory::DEFAULT_INT_CODE
    ) {
        static::assertSame($expectedMessage, $result->getMessage());
        static::assertSame($expectedMessage, sprintf($sprintf, ...$args));
        static::assertSame($code, $result->getCode());

        $resultPrevious = $result->getPrevious();

        static::assertSame(gettype($expectedPrevious), gettype($resultPrevious));

        if ( ! is_null($previousType)) {
            static::assertInstanceOf($previousType, $expectedPrevious);
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
    * @psalm-param class-string<Throwable>|null $previousType
    *
    * @return Throwable|null
    */
    protected static function MaybeObtainThrowable(
        string $previousType = null,
        string $previousMessage = '',
        int $previousCode = SprintfExceptionFactory::DEFAULT_INT_CODE
    ) {
        $previous = null;

        if ( ! is_null($previousType)) {
            $previous = new $previousType($previousMessage, $previousCode);
        }

        return $previous;
    }
}
