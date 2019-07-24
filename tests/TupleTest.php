<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace BakameTest\Period\Visualizer;

use ArrayObject;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\LatinLetter;
use Bakame\Period\Visualizer\Tuple;
use DateTimeImmutable;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\Tuple;
 */
final class TupleTest extends TestCase
{
    public function testFromSequenceConstructor(): void
    {
        $periodA = Period::fromDay(2018, 3, 15);
        $periodB = Period::fromDay(2019, 3, 15);
        $labelGenerator = new LatinLetter('A');
        $sequence = new Sequence($periodA, $periodB);
        $tuple = Tuple::fromSequence($sequence, $labelGenerator);
        $arr = iterator_to_array($tuple);

        self::assertCount(2, $tuple);
        self::assertSame('B', $arr[1][0]);
        self::assertTrue($periodB->equals($arr[1][1]));

        $emptyTuple = Tuple::fromSequence(new Sequence(), $labelGenerator);
        self::assertCount(0, $emptyTuple);
        self::assertTrue($emptyTuple->isEmpty());
    }

    /**
     * @dataProvider provideIterableStructure
     */
    public function testFromIterableConstructor(iterable $input, int $expectedCount, bool $isEmpty, bool $boundaryIsNull): void
    {
        $tuple = Tuple::fromCollection($input);
        self::assertCount($expectedCount, $tuple);
        self::assertSame($isEmpty, $tuple->isEmpty());
        self::assertSame($boundaryIsNull, null === $tuple->boundaries());
    }

    public function provideIterableStructure(): iterable
    {
        return [
            'empty structure' => [
                'input' => [],
                'expectedCount' => 0,
                'isEmpty' => true,
                'boundaryIsNull' => true,
            ],
            'single array' => [
                'input' => [Period::fromDay(2019, 3, 15)],
                'expectedCount' => 1,
                'isEmpty' => false,
                'boundaryIsNull' => false,
            ],
            'using an iterator' => [
                'input' => new ArrayObject([Period::fromDay(2019, 3, 15)]),
                'expectedCount' => 1,
                'isEmpty' => false,
                'boundaryIsNull' => false,
            ],
            'using a direct sequence' => [
                'input' => new Sequence(
                    Period::fromDay(2018, 9, 10),
                    Period::fromDay(2019, 10, 11)
                ),
                'expectedCount' => 2,
                'isEmpty' => false,
                'boundaryIsNull' => false,
            ],
            'using a wrapped sequence' => [
                'input' => [new Sequence(
                    Period::fromDay(2018, 9, 10),
                    Period::fromDay(2019, 10, 11)
                )],
                'expectedCount' => 1,
                'isEmpty' => false,
                'boundaryIsNull' => false,
            ],
        ];
    }

    public function testAppendPairs(): void
    {
        $tuple = new Tuple([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
            [new DateTimeImmutable(), new Sequence(new Period('2018-01-15', '2018-02-01'))],
            ['C', 'foo bar'],
        ]);

        self::assertCount(2, $tuple);
    }

    public function testLabelizePairs(): void
    {
        $tuple = new Tuple([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);
        self::assertSame(['A', 'B'], $tuple->labels());
        self::assertSame(1, $tuple->labelMaxLength());

        $newTuple = $tuple->labelize(new DecimalNumber(42));
        self::assertSame(['42', '43'], $newTuple->labels());
        self::assertSame($tuple->items(), $newTuple->items());
        self::assertSame(2, $newTuple->labelMaxLength());
    }

    public function testLabelizePairsReturnsSameInstance(): void
    {
        $tuple = new Tuple([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);

        self::assertEquals($tuple, $tuple->labelize(new LatinLetter()));

        $emptyTuple = new Tuple();
        self::assertEquals($emptyTuple, $emptyTuple->labelize(new DecimalNumber(42)));
    }
}
