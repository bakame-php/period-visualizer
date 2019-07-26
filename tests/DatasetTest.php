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
use Bakame\Period\Visualizer\Dataset;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\LatinLetter;
use DateTimeImmutable;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\Dataset;
 */
final class DatasetTest extends TestCase
{
    public function testFromSequenceConstructor(): void
    {
        $periodA = Period::fromDay(2018, 3, 15);
        $periodB = Period::fromDay(2019, 3, 15);
        $labelGenerator = new LatinLetter('A');
        $sequence = new Sequence($periodA, $periodB);
        $dataset = Dataset::fromSequence($sequence, $labelGenerator);
        $arr = iterator_to_array($dataset);

        self::assertCount(2, $dataset);
        self::assertSame('B', $arr[1][0]);
        self::assertTrue($periodB->equals($arr[1][1]));

        $emptyDataset = Dataset::fromSequence(new Sequence(), $labelGenerator);
        self::assertCount(0, $emptyDataset);
        self::assertTrue($emptyDataset->isEmpty());
    }

    /**
     * @dataProvider provideIterableStructure
     */
    public function testFromIterableConstructor(iterable $input, int $expectedCount, bool $isEmpty, bool $boundaryIsNull): void
    {
        $dataset = Dataset::fromCollection($input);
        self::assertCount($expectedCount, $dataset);
        self::assertSame($isEmpty, $dataset->isEmpty());
        self::assertSame($boundaryIsNull, null === $dataset->boundaries());
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
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
            [new DateTimeImmutable(), new Sequence(new Period('2018-01-15', '2018-02-01'))],
            ['C', 'foo bar'],
        ]);

        self::assertCount(2, $dataset);
    }

    public function testLabelizePairs(): void
    {
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);
        self::assertSame(['A', 'B'], $dataset->labels());
        self::assertSame(1, $dataset->labelMaxLength());

        $newDataset = $dataset->labelize(new DecimalNumber(42));
        self::assertSame(['42', '43'], $newDataset->labels());
        self::assertSame($dataset->items(), $newDataset->items());
        self::assertSame(2, $newDataset->labelMaxLength());
    }

    public function testLabelizePairsReturnsSameInstance(): void
    {
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);

        self::assertEquals($dataset, $dataset->labelize(new LatinLetter()));

        $emptyDataset = new Dataset();
        self::assertEquals($emptyDataset, $emptyDataset->labelize(new DecimalNumber(42)));
    }

    public function testEmptyInstance(): void
    {
        $dataset = new Dataset();
        self::assertSame(0, $dataset->labelMaxLength());
        self::assertSame([], $dataset->items());
        self::assertSame([], $dataset->labels());
    }
}
