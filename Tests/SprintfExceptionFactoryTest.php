<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\SprintfExceptionFactory\Tests;

use BadMethodCallException;
use Exception;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use SignpostMarv\SprintfExceptionFactory\SprintfExceptionFactory;
use Throwable;

class SprintfExceptionFactoryTest extends TestCase
{
	const ARG_SECOND = 1;

	const REGEX_MATCH = 1;

	/**
	 * @psalm-return array<int, array{0:string, 1:class-string<\Exception>, 2:string, 3:array<int, string|int|float>, 4:int, 5:class-string<Throwable>|null, 6:string, 7:int}>
	 */
	public function DataProviderException() : array
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
			[
				'foo bar',
				RuntimeException::class,
				'foo %s',
				[
					'bar',
				],
				SprintfExceptionFactory::DEFAULT_INT_CODE,
				null,
				'',
				SprintfExceptionFactory::DEFAULT_INT_CODE,
			],
			[
				'foo bar',
				BadMethodCallException::class,
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

	/**
	 * @psalm-return Generator<int, array{0:string, 1:class-string<InvalidArgumentException>, 2:string, 3:array<int, string|int|float>, 4:int, 5:class-string<Throwable>|null, 6:string, 7:int}, mixed, void>
	 */
	public function DataProviderInvalidArgumentException() : Generator
	{
		foreach ($this->DataProviderException() as $args) {
			if (is_a($args[self::ARG_SECOND], InvalidArgumentException::class, true)) {
				yield $args;
			}
		}
	}

	/**
	 * @psalm-param class-string<Exception> $type
	 * @psalm-param class-string<Throwable>|null $previousType
	 *
	 * @param array<int, string|int|float> $args
	 *
	 * @dataProvider DataProviderInvalidArgumentException
	 */
	public function test_exception(
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

		if (is_null($previousType)) {
			static::assertNull($previous);
		}

		$result = SprintfExceptionFactory::Exception(
			$type,
			$code,
			$previous,
			$type,
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
	 * @psalm-return Generator<int, array{0:ReflectionMethod, 1:string, 2:class-string<\Exception>, 3:string, 4:array<int, string|int|float>, 5:int, 6:class-string<Throwable>|null, 7:string, 8:int}, mixed, void>
	 */
	public function DataProviderTestFactoryMethod() : Generator
	{
		$map_types = static::MapTypesToReflectors();

		foreach ($this->DataProviderException() as $args) {
			if (isset($map_types[$args[self::ARG_SECOND]])) {
				array_unshift($args, $map_types[$args[self::ARG_SECOND]]);

				/**
				 * @var array{0:ReflectionMethod, 1:string, 2:class-string<\Exception>, 3:string, 4:array<int, string|int|float>, 5:int, 6:class-string<Throwable>|null, 7:string, 8:int}
				 */
				$args = $args;

				yield $args;
			}
		}
	}

	/**
	 * @psalm-param class-string<Exception> $type
	 * @psalm-param class-string<Throwable>|null $previousType
	 *
	 * @param array<int, string|int|float> $args
	 *
	 * @dataProvider DataProviderTestFactoryMethod
	 */
	public function test_factory_method(
		ReflectionMethod $reflector,
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

		if (is_null($previousType)) {
			static::assertNull($previous);
		}

		/**
		 * @var Throwable
		 */
		$result = $reflector->invoke(
			null,
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
	}

	/**
	 * @return Generator<int, ReflectionMethod, mixed, void>
	 */
	protected static function YieldMethodsMatchingThrowable() : Generator
	{
		$factory_reflector = new ReflectionClass(SprintfExceptionFactory::class);

		$methods = $factory_reflector->getMethods(
			ReflectionMethod::IS_STATIC |
			ReflectionMethod::IS_PUBLIC
		);

		foreach ($methods as $reflector) {
			if (is_a($reflector->getName(), Throwable::class, true)) {
				yield $reflector;
			}
		}
	}

	/**
	 * @return array<class-string<Throwable>, ReflectionMethod>
	 */
	protected static function MapTypesToReflectors() : array
	{
		/**
		 * @var array<class-string<Throwable>, ReflectionMethod>
		 */
		$map_types = [];

		foreach (static::YieldMethodsMatchingThrowable() as $reflector) {
			$docblock = $reflector->getDocComment();

			if (
				is_string($docblock) &&
				self::REGEX_MATCH === preg_match(
					'/\* @throws ([^\ ]+).+[\r\n]/',
					$docblock,
					$matches
				) &&
				$reflector->getName() === $matches[self::ARG_SECOND]
			) {
				/**
				 * @psalm-var class-string<Throwable>
				 */
				$type = $matches[self::ARG_SECOND];

				$map_types[$type] = $reflector;
			}
		}

		unset($map_types[Exception::class]);

		return $map_types;
	}

	/**
	 * @template T as Throwable
	 *
	 * @psalm-param class-string<T> $type
	 * @psalm-param class-string<Throwable>|null $previousType
	 *
	 * @param array<int, string|int|float> $args
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
	) : void {
		static::assertInstanceOf($type, $result);
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
