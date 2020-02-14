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
	 * @psalm-return Generator<int, array{0:string, 1:class-string<Exception>, 2:string, 3:array<int, string|int|float>, 4:int, 5:class-string<Throwable>|null, 6:string, 7:int}, mixed, void>
	 */
	public function DataProviderInvalidArgumentExceptionBad() : Generator
	{
		foreach ($this->DataProviderException() as $args) {
			if ( ! is_a($args[self::ARG_SECOND], InvalidArgumentException::class, true)) {
				yield $args;
			}
		}
	}

	/**
	 * @psalm-param class-string<InvalidArgumentException> $type
	 * @psalm-param class-string<Throwable>|null $previousType
	 *
	 * @param array<int, string|int|float> $args
	 *
	 * @dataProvider DataProviderInvalidArgumentException
	 */
	public function test_invalid_argument_exception(
		string $expectedMessage,
		string $type,
		string $sprintf,
		array $args,
		int $code = SprintfExceptionFactory::DEFAULT_INT_CODE,
		string $previousType = null,
		string $previousMessage = '',
		int $previousCode = SprintfExceptionFactory::DEFAULT_INT_CODE
	) : void {
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

		$this->test_exception(
			$expectedMessage,
			$type,
			$sprintf,
			$args,
			$code,
			$previousType,
			$previousMessage,
			$previousCode
		);

		$this->test_invalid_argument_exception_fails(
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
	 * @psalm-param class-string<Throwable> $type
	 * @psalm-param class-string<Throwable>|null $previousType
	 *
	 * @param array<int, string|int|float> $args
	 *
	 * @dataProvider DataProviderInvalidArgumentExceptionBad
	 */
	public function test_invalid_argument_exception_fails(
		string $expectedMessage,
		string $type,
		string $sprintf,
		array $args,
		int $code = SprintfExceptionFactory::DEFAULT_INT_CODE,
		string $previousType = null,
		string $previousMessage = '',
		int $previousCode = SprintfExceptionFactory::DEFAULT_INT_CODE
	) : void {
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

		/**
		 * @psalm-var class-string<InvalidArgumentException>
		 */
		$type = $type;

		SprintfExceptionFactory::InvalidArgumentException(
			$type,
			$code,
			$previous,
			$sprintf,
			...$args
		);
	}
}
